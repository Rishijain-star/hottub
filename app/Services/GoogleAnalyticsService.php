<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\ApiCore\ApiException;
use Throwable;

/**
 * GA4 reporting via Google Analytics Data API (service account).
 */
class GoogleAnalyticsService
{
    protected ?BetaAnalyticsDataClient $client = null;

    /**
     * Whether property ID and readable credentials are present.
     */
    public function isConfigured(): bool
    {
        $propertyId = config('analytics.property_id');
        $credentials = config('analytics.credentials_path');

        return ! empty($propertyId)
            && is_string($credentials)
            && $credentials !== ''
            && is_file($credentials)
            && is_readable($credentials);
    }

    /**
     * Which isConfigured() checks failed (for admin debug endpoint only).
     *
     * @return array<string, bool>
     */
    public function configurationDiagnostics(): array
    {
        $propertyId = config('analytics.property_id');
        $credentials = config('analytics.credentials_path');

        return [
            'property_id_present' => ! empty($propertyId),
            'credentials_path_present' => is_string($credentials) && $credentials !== '',
            'credentials_file_exists' => is_string($credentials) && $credentials !== '' && is_file($credentials),
            'credentials_readable' => is_string($credentials) && $credentials !== '' && is_readable($credentials),
            'is_configured' => $this->isConfigured(),
        ];
    }

    /**
     * GA4 property resource name, e.g. properties/123456789.
     */
    public function propertyResourceName(): ?string
    {
        $id = config('analytics.property_id');

        return $id ? 'properties/' . $id : null;
    }

    /**
     * Lazy-init Data API client using service account JSON.
     */
    protected function client(): BetaAnalyticsDataClient
    {
        if ($this->client === null) {
            $options = [];
            $path = config('analytics.credentials_path');
            if ($path) {
                $options['credentials'] = $path;
            }

            $this->client = new BetaAnalyticsDataClient($options);
        }

        return $this->client;
    }

    /**
     * Test metrics: active users, sessions, page views (screenPageViews) for a date range.
     *
     * GA4 date literals: today, yesterday, 7daysAgo, 30daysAgo, or YYYY-MM-DD.
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getTestMetrics(string $startDate = '7daysAgo', string $endDate = 'today'): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Google Analytics is not configured. Set GOOGLE_ANALYTICS_PROPERTY_ID and GOOGLE_APPLICATION_CREDENTIALS in .env, and ensure the credentials file is readable.',
                'code' => 'not_configured',
            ];
        }

        try {
            $request = (new RunReportRequest())
                ->setProperty($this->propertyResourceName())
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews']),
                ]);

            $response = $this->client()->runReport($request);

            [$activeUsers, $sessions, $pageViews] = $this->extractMetricTotals($response, 3);

            return [
                'ok' => true,
                'data' => [
                    'active_users' => $activeUsers,
                    'sessions' => $sessions,
                    'page_views' => $pageViews,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'property_id' => config('analytics.property_id'),
                ],
            ];
        } catch (ApiException $e) {
            return [
                'ok' => false,
                'error' => $this->friendlyApiError($e),
                'code' => $e->getCode(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => 'exception',
            ];
        }
    }

    /**
     * Read aggregated metric values from totals row, else first data row.
     *
     * @return array<int, int>
     */
    protected function extractMetricTotals(RunReportResponse $response, int $count): array
    {
        $values = array_fill(0, $count, 0);

        $totals = $response->getTotals();
        if ($totals->count() > 0) {
            foreach ($totals[0]->getMetricValues() as $i => $metricValue) {
                if ($i < $count) {
                    $values[$i] = (int) $metricValue->getValue();
                }
            }

            return $values;
        }

        $rows = $response->getRows();
        if ($rows->count() > 0) {
            foreach ($rows[0]->getMetricValues() as $i => $metricValue) {
                if ($i < $count) {
                    $values[$i] = (int) $metricValue->getValue();
                }
            }
        }

        return $values;
    }

    /**
     * Step 1 dashboard: Traffic & Audience, Traffic Sources, Content Performance.
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getDashboardStep1(int $days = 30): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Google Analytics is not configured.',
                'code' => 'not_configured',
            ];
        }

        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        [$startDate, $endDate] = $this->dateRangeFromDays($days);

        try {
            return [
                'ok' => true,
                'data' => [
                    'property_id' => config('analytics.property_id'),
                    'range' => [
                        'days' => $days,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'label' => "Last {$days} days",
                    ],
                    'traffic_audience' => $this->fetchTrafficAudience($startDate, $endDate),
                    'traffic_sources' => $this->fetchTrafficSources($startDate, $endDate),
                    'content_performance' => $this->fetchContentPerformance($startDate, $endDate),
                ],
            ];
        } catch (ApiException $e) {
            return [
                'ok' => false,
                'error' => $this->friendlyApiError($e),
                'code' => $e->getCode(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => 'exception',
            ];
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function dateRangeFromDays(int $days): array
    {
        return match ($days) {
            7 => ['7daysAgo', 'today'],
            90 => ['90daysAgo', 'today'],
            default => ['30daysAgo', 'today'],
        };
    }

    /**
     * @param  array<int, string>  $metricNames
     * @param  array<int, string>  $dimensionNames
     */
    protected function runReport(
        array $metricNames,
        array $dimensionNames,
        string $startDate,
        string $endDate,
        ?int $limit = null,
        ?string $orderByMetric = null,
    ): RunReportResponse {
        $request = (new RunReportRequest())
            ->setProperty($this->propertyResourceName())
            ->setDateRanges([
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ])
            ->setMetrics(array_map(fn (string $name) => new Metric(['name' => $name]), $metricNames))
            ->setDimensions(array_map(fn (string $name) => new Dimension(['name' => $name]), $dimensionNames));

        if ($limit !== null) {
            $request->setLimit($limit);
        }

        if ($orderByMetric !== null) {
            $request->setOrderBys([
                (new OrderBy())
                    ->setMetric(new MetricOrderBy(['metric_name' => $orderByMetric]))
                    ->setDesc(true),
            ]);
        }

        return $this->client()->runReport($request);
    }

    /**
     * @return array<int, array{dimensions: array<int, string>, metrics: array<int, float>}>
     */
    protected function parseReportRows(RunReportResponse $response): array
    {
        $parsed = [];
        foreach ($response->getRows() as $row) {
            $dimensions = [];
            foreach ($row->getDimensionValues() as $dimensionValue) {
                $dimensions[] = $dimensionValue->getValue();
            }
            $metrics = [];
            foreach ($row->getMetricValues() as $metricValue) {
                $metrics[] = (float) $metricValue->getValue();
            }
            $parsed[] = [
                'dimensions' => $dimensions,
                'metrics' => $metrics,
            ];
        }

        return $parsed;
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchTrafficAudience(string $startDate, string $endDate): array
    {
        $summary = $this->runReport(
            [
                'activeUsers',
                'sessions',
                'newUsers',
                'screenPageViewsPerSession',
                'averageSessionDuration',
                'engagementRate',
                'bounceRate',
            ],
            [],
            $startDate,
            $endDate,
        );

        $summaryMetrics = $this->extractMetricTotalsFloat($summary, 7);
        $returningUsers = max(0, (int) $summaryMetrics[0] - (int) $summaryMetrics[2]);

        $newVsReturning = $this->runReport(
            ['activeUsers'],
            ['newVsReturning'],
            $startDate,
            $endDate,
        );
        $newVisitors = 0;
        $returningVisitors = 0;
        foreach ($this->parseReportRows($newVsReturning) as $row) {
            $label = strtolower($row['dimensions'][0] ?? '');
            $users = (int) ($row['metrics'][0] ?? 0);
            if (str_contains($label, 'new')) {
                $newVisitors = $users;
            } elseif (str_contains($label, 'return')) {
                $returningVisitors = $users;
            }
        }

        $devicesResponse = $this->runReport(
            ['sessions'],
            ['deviceCategory'],
            $startDate,
            $endDate,
        );
        $devices = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0, 'other' => 0];
        foreach ($this->parseReportRows($devicesResponse) as $row) {
            $category = strtolower($row['dimensions'][0] ?? 'other');
            $sessions = (int) ($row['metrics'][0] ?? 0);
            if (isset($devices[$category])) {
                $devices[$category] += $sessions;
            } else {
                $devices['other'] += $sessions;
            }
        }

        $geoResponse = $this->runReport(
            ['sessions'],
            ['country', 'region', 'city'],
            $startDate,
            $endDate,
            100,
            'sessions',
        );
        $geoRows = [];
        foreach ($this->parseReportRows($geoResponse) as $row) {
            $country = $row['dimensions'][0] ?? '';
            if (! $this->isUnitedKingdom($country)) {
                continue;
            }
            $region = $row['dimensions'][1] ?? '';
            $city = $row['dimensions'][2] ?? '';
            $label = trim($city !== '' && $city !== '(not set)' ? "{$city}, {$region}" : $region);
            if ($label === '' || $label === '(not set)') {
                continue;
            }
            $geoRows[] = [
                'location' => $label,
                'sessions' => (int) ($row['metrics'][0] ?? 0),
            ];
        }
        usort($geoRows, fn ($a, $b) => $b['sessions'] <=> $a['sessions']);
        $geoRows = array_slice($geoRows, 0, 10);

        return [
            'total_users' => (int) $summaryMetrics[0],
            'sessions' => (int) $summaryMetrics[1],
            'new_users' => $newVisitors > 0 ? $newVisitors : (int) $summaryMetrics[2],
            'returning_users' => $returningVisitors > 0 ? $returningVisitors : $returningUsers,
            'pages_per_session' => round($summaryMetrics[3], 2),
            'avg_engagement_time_seconds' => (int) round($summaryMetrics[4]),
            'engagement_rate_percent' => round($summaryMetrics[5] * 100, 1),
            'bounce_rate_percent' => round($summaryMetrics[6] * 100, 1),
            'devices' => $devices,
            'geography' => $geoRows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchTrafficSources(string $startDate, string $endDate): array
    {
        $channelResponse = $this->runReport(
            ['sessions'],
            ['sessionDefaultChannelGroup'],
            $startDate,
            $endDate,
        );

        $buckets = [
            'organic' => 0,
            'direct' => 0,
            'social' => 0,
            'referral' => 0,
            'paid' => 0,
            'other' => 0,
        ];

        foreach ($this->parseReportRows($channelResponse) as $row) {
            $channel = $row['dimensions'][0] ?? 'other';
            $sessions = (int) ($row['metrics'][0] ?? 0);
            $bucket = $this->mapChannelToBucket($channel);
            $buckets[$bucket] += $sessions;
        }

        $referrersResponse = $this->runReport(
            ['sessions'],
            ['sessionSource'],
            $startDate,
            $endDate,
            25,
            'sessions',
        );

        $referrers = [];
        foreach ($this->parseReportRows($referrersResponse) as $row) {
            $source = trim($row['dimensions'][0] ?? '');
            if ($source === '' || $source === '(direct)' || $source === '(not set)') {
                continue;
            }
            $referrers[] = [
                'source' => $source,
                'sessions' => (int) ($row['metrics'][0] ?? 0),
            ];
        }
        $referrers = array_slice($referrers, 0, 10);

        return [
            'channels' => $buckets,
            'top_referrers' => $referrers,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchContentPerformance(string $startDate, string $endDate): array
    {
        $pagesResponse = $this->runReport(
            ['screenPageViews', 'userEngagementDuration'],
            ['pagePath'],
            $startDate,
            $endDate,
            20,
            'screenPageViews',
        );

        $mostVisited = [];
        foreach ($this->parseReportRows($pagesResponse) as $row) {
            $path = $row['dimensions'][0] ?? '/';
            $views = (int) ($row['metrics'][0] ?? 0);
            $engagement = (float) ($row['metrics'][1] ?? 0);
            $mostVisited[] = [
                'page' => $path,
                'views' => $views,
                'avg_time_on_page_seconds' => $views > 0 ? (int) round($engagement / $views) : 0,
            ];
        }

        $entryResponse = $this->runReport(
            ['sessions'],
            ['landingPage'],
            $startDate,
            $endDate,
            20,
            'sessions',
        );
        $entryPages = [];
        foreach ($this->parseReportRows($entryResponse) as $row) {
            $entryPages[] = [
                'page' => $row['dimensions'][0] ?? '/',
                'sessions' => (int) ($row['metrics'][0] ?? 0),
            ];
        }

        // GA4 Data API has no "exits" metric; rank pages by sessions on pagePath instead.
        $exitResponse = $this->runReport(
            ['sessions'],
            ['pagePath'],
            $startDate,
            $endDate,
            20,
            'sessions',
        );
        $exitPages = [];
        foreach ($this->parseReportRows($exitResponse) as $row) {
            $exitPages[] = [
                'page' => $row['dimensions'][0] ?? '/',
                'exits' => (int) ($row['metrics'][0] ?? 0),
            ];
        }

        return [
            'most_visited' => $mostVisited,
            'entry_pages' => $entryPages,
            'exit_pages' => $exitPages,
        ];
    }

    protected function mapChannelToBucket(string $channel): string
    {
        $normalized = strtolower(trim($channel));

        if (str_contains($normalized, 'organic') && str_contains($normalized, 'search')) {
            return 'organic';
        }
        if ($normalized === 'direct') {
            return 'direct';
        }
        if (str_contains($normalized, 'social')) {
            return 'social';
        }
        if (str_contains($normalized, 'referral')) {
            return 'referral';
        }
        if (str_contains($normalized, 'paid') || str_contains($normalized, 'cpc') || str_contains($normalized, 'display')) {
            return 'paid';
        }

        return 'other';
    }

    protected function isUnitedKingdom(string $country): bool
    {
        $normalized = strtolower(trim($country));

        return in_array($normalized, ['united kingdom', 'uk', 'gb', 'great britain'], true);
    }

    /**
     * @return array<int, float>
     */
    protected function extractMetricTotalsFloat(RunReportResponse $response, int $count): array
    {
        $values = array_fill(0, $count, 0.0);

        $totals = $response->getTotals();
        if ($totals->count() > 0) {
            foreach ($totals[0]->getMetricValues() as $i => $metricValue) {
                if ($i < $count) {
                    $values[$i] = (float) $metricValue->getValue();
                }
            }

            return $values;
        }

        $rows = $response->getRows();
        if ($rows->count() > 0) {
            foreach ($rows[0]->getMetricValues() as $i => $metricValue) {
                if ($i < $count) {
                    $values[$i] = (float) $metricValue->getValue();
                }
            }
        }

        return $values;
    }

    /**
     * Surface permission / config hints for common API failures.
     */
    protected function friendlyApiError(ApiException $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'PERMISSION_DENIED') || str_contains($message, '403')) {
            return $message . ' — Add the service account email as a Viewer on the GA4 property (Admin → Property access management).';
        }

        if (str_contains($message, 'INVALID_ARGUMENT') || str_contains($message, 'not found')) {
            return $message . ' — Verify GOOGLE_ANALYTICS_PROPERTY_ID matches your GA4 property ID.';
        }

        return $message;
    }

    /**
     * Content improvements: product pages, site search, scroll depth.
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getContentImprovements(int $days = 30): array
    {
        return $this->runExtensionReport($days, function (string $startDate, string $endDate) {
            $productFilter = $this->pagePathContainsOrFilter(['/hot-tubs/', '/swim-spas/']);
            $productResponse = $this->runReportWithDimensionFilter(
                ['screenPageViews', 'sessions'],
                ['pagePath'],
                $startDate,
                $endDate,
                $productFilter,
                20,
                'screenPageViews',
            );

            $topProductPages = [];
            foreach ($this->parseReportRows($productResponse) as $row) {
                $topProductPages[] = [
                    'page' => $row['dimensions'][0] ?? '/',
                    'views' => (int) ($row['metrics'][0] ?? 0),
                    'sessions' => (int) ($row['metrics'][1] ?? 0),
                ];
            }

            $searchResponse = $this->runReportWithDimensionFilter(
                ['sessions', 'eventCount'],
                ['searchTerm'],
                $startDate,
                $endDate,
                $this->dimensionNotEmptyFilter('searchTerm'),
                15,
                'sessions',
            );

            $searchTerms = [];
            foreach ($this->parseReportRows($searchResponse) as $row) {
                $term = trim($row['dimensions'][0] ?? '');
                if ($term === '' || $term === '(not set)') {
                    continue;
                }
                $searchTerms[] = [
                    'term' => $term,
                    'sessions' => (int) ($row['metrics'][0] ?? 0),
                    'events' => (int) ($row['metrics'][1] ?? 0),
                ];
            }

            $scrollPercent = null;
            try {
                $scrollResponse = $this->runReport(
                    ['scrolledUsers', 'activeUsers'],
                    [],
                    $startDate,
                    $endDate,
                );
                $scrollMetrics = $this->extractMetricTotalsFloat($scrollResponse, 2);
                $scrolled = (float) ($scrollMetrics[0] ?? 0);
                $active = (float) ($scrollMetrics[1] ?? 0);
                $scrollPercent = $active > 0 ? round(($scrolled / $active) * 100, 1) : null;
            } catch (Throwable) {
                $scrollPercent = null;
            }

            return [
                'top_product_pages' => $topProductPages,
                'site_search_terms' => $searchTerms,
                'scroll_depth_percent' => $scrollPercent,
            ];
        });
    }

    /**
     * Browser compatibility: sessions by browser.
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getBrowserCompatibility(int $days = 30): array
    {
        return $this->runExtensionReport($days, function (string $startDate, string $endDate) {
            $response = $this->runReport(
                ['sessions'],
                ['browser'],
                $startDate,
                $endDate,
                15,
                'sessions',
            );

            $browsers = [];
            foreach ($this->parseReportRows($response) as $row) {
                $browser = trim($row['dimensions'][0] ?? '');
                if ($browser === '' || $browser === '(not set)') {
                    continue;
                }
                $browsers[] = [
                    'browser' => $browser,
                    'sessions' => (int) ($row['metrics'][0] ?? 0),
                ];
            }

            return ['browsers' => $browsers];
        });
    }

    /**
     * Broken links / 404 pages (pagePath or pageTitle contains "404").
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getBrokenLinks404(int $days = 30): array
    {
        return $this->runExtensionReport($days, function (string $startDate, string $endDate) {
            $filter = new FilterExpression([
                'or_group' => new FilterExpressionList([
                    'expressions' => [
                        $this->dimensionContainsFilter('pagePath', '404'),
                        $this->dimensionContainsFilter('pageTitle', '404'),
                    ],
                ]),
            ]);

            $response = $this->runReportWithDimensionFilter(
                ['screenPageViews', 'sessions'],
                ['pagePath', 'pageTitle'],
                $startDate,
                $endDate,
                $filter,
                20,
                'screenPageViews',
            );

            $pages = [];
            foreach ($this->parseReportRows($response) as $row) {
                $pages[] = [
                    'page_path' => $row['dimensions'][0] ?? '/',
                    'page_title' => $row['dimensions'][1] ?? '—',
                    'views' => (int) ($row['metrics'][0] ?? 0),
                    'sessions' => (int) ($row['metrics'][1] ?? 0),
                ];
            }

            return ['pages' => $pages];
        });
    }

    /**
     * Returning visitor frequency (newVsReturning + sessionCampaignName for returning).
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getReturningVisitorFrequency(int $days = 30): array
    {
        return $this->runExtensionReport($days, function (string $startDate, string $endDate) {
            $typeResponse = $this->runReport(
                ['sessions'],
                ['newVsReturning'],
                $startDate,
                $endDate,
            );

            $byType = [];
            foreach ($this->parseReportRows($typeResponse) as $row) {
                $label = $row['dimensions'][0] ?? 'unknown';
                $byType[] = [
                    'type' => $label,
                    'sessions' => (int) ($row['metrics'][0] ?? 0),
                ];
            }

            $returningFilter = new FilterExpression([
                'filter' => new Filter([
                    'field_name' => 'newVsReturning',
                    'string_filter' => new StringFilter([
                        'match_type' => StringFilter\MatchType::EXACT,
                        'value' => 'returning',
                    ]),
                ]),
            ]);

            $campaignResponse = $this->runReportWithDimensionFilter(
                ['sessions'],
                ['sessionCampaignName'],
                $startDate,
                $endDate,
                $returningFilter,
                15,
                'sessions',
            );

            $returningCampaigns = [];
            foreach ($this->parseReportRows($campaignResponse) as $row) {
                $campaign = trim($row['dimensions'][0] ?? '');
                if ($campaign === '' || $campaign === '(not set)') {
                    continue;
                }
                $returningCampaigns[] = [
                    'campaign' => $campaign,
                    'sessions' => (int) ($row['metrics'][0] ?? 0),
                ];
            }

            return [
                'by_visitor_type' => $byType,
                'returning_by_campaign' => $returningCampaigns,
            ];
        });
    }

    /**
     * Popular product category paths (/hot-tubs/, /swim-spas/).
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getPopularFilterCategories(int $days = 30): array
    {
        return $this->runExtensionReport($days, function (string $startDate, string $endDate) {
            $filter = $this->pagePathContainsOrFilter(['/hot-tubs', '/swim-spas']);

            $response = $this->runReportWithDimensionFilter(
                ['screenPageViews', 'sessions'],
                ['pagePath'],
                $startDate,
                $endDate,
                $filter,
                20,
                'screenPageViews',
            );

            $categories = [];
            foreach ($this->parseReportRows($response) as $row) {
                $path = $row['dimensions'][0] ?? '/';
                $categories[] = [
                    'page' => $path,
                    'views' => (int) ($row['metrics'][0] ?? 0),
                    'sessions' => (int) ($row['metrics'][1] ?? 0),
                ];
            }

            return ['categories' => $categories];
        });
    }

    /**
     * @param  callable(string, string): array<string, mixed>  $callback
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    protected function runExtensionReport(int $days, callable $callback): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Google Analytics is not configured.',
                'code' => 'not_configured',
            ];
        }

        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        [$startDate, $endDate] = $this->dateRangeFromDays($days);

        try {
            return [
                'ok' => true,
                'data' => $callback($startDate, $endDate),
            ];
        } catch (ApiException $e) {
            return [
                'ok' => false,
                'error' => $this->friendlyApiError($e),
                'code' => $e->getCode(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => 'exception',
            ];
        }
    }

    /**
     * @param  array<int, string>  $metricNames
     * @param  array<int, string>  $dimensionNames
     */
    protected function runReportWithDimensionFilter(
        array $metricNames,
        array $dimensionNames,
        string $startDate,
        string $endDate,
        FilterExpression $dimensionFilter,
        ?int $limit = null,
        ?string $orderByMetric = null,
    ): RunReportResponse {
        $request = (new RunReportRequest())
            ->setProperty($this->propertyResourceName())
            ->setDateRanges([
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ])
            ->setMetrics(array_map(fn (string $name) => new Metric(['name' => $name]), $metricNames))
            ->setDimensions(array_map(fn (string $name) => new Dimension(['name' => $name]), $dimensionNames))
            ->setDimensionFilter($dimensionFilter);

        if ($limit !== null) {
            $request->setLimit($limit);
        }

        if ($orderByMetric !== null) {
            $request->setOrderBys([
                (new OrderBy())
                    ->setMetric(new MetricOrderBy(['metric_name' => $orderByMetric]))
                    ->setDesc(true),
            ]);
        }

        return $this->client()->runReport($request);
    }

    protected function dimensionContainsFilter(string $fieldName, string $value): FilterExpression
    {
        return new FilterExpression([
            'filter' => new Filter([
                'field_name' => $fieldName,
                'string_filter' => new StringFilter([
                    'match_type' => StringFilter\MatchType::CONTAINS,
                    'value' => $value,
                ]),
            ]),
        ]);
    }

    protected function dimensionNotEmptyFilter(string $fieldName): FilterExpression
    {
        return new FilterExpression([
            'not_expression' => new FilterExpression([
                'filter' => new Filter([
                    'field_name' => $fieldName,
                    'string_filter' => new StringFilter([
                        'match_type' => StringFilter\MatchType::EXACT,
                        'value' => '(not set)',
                    ]),
                ]),
            ]),
        ]);
    }

    /**
     * @param  array<int, string>  $fragments
     */
    protected function pagePathContainsOrFilter(array $fragments): FilterExpression
    {
        $expressions = [];
        foreach ($fragments as $fragment) {
            $expressions[] = $this->dimensionContainsFilter('pagePath', $fragment);
        }

        return new FilterExpression([
            'or_group' => new FilterExpressionList([
                'expressions' => $expressions,
            ]),
        ]);
    }
}
