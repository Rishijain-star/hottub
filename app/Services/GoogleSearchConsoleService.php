<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Throwable;

/**
 * Google Search Console — Search Analytics API via OAuth 2.0 refresh token.
 */
class GoogleSearchConsoleService
{
    protected ?SearchConsole $service = null;

    public function isConfigured(): bool
    {
        return filled(config('search_console.site_url'))
            && filled(config('search_console.refresh_token'))
            && filled(config('search_console.oauth_client_json'))
            && is_file(config('search_console.oauth_client_json'));
    }

    /**
     * URL to start OAuth consent (admin visits /admin/analytics/gsc-auth).
     */
    public function createAuthorizationUrl(): string
    {
        $client = $this->createOAuthClient();
        $client->setPrompt('consent');
        $client->setAccessType('offline');

        return $client->createAuthUrl();
    }

    /**
     * Exchange one-time authorization code for a long-lived refresh token.
     */
    public function exchangeAuthorizationCode(string $code): string
    {
        $client = $this->createOAuthClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException($token['error_description'] ?? $token['error']);
        }

        $refreshToken = $token['refresh_token'] ?? null;
        if (! filled($refreshToken)) {
            throw new \RuntimeException(
                'No refresh token returned. Revoke app access at https://myaccount.google.com/permissions and try again with prompt=consent.'
            );
        }

        return (string) $refreshToken;
    }

    /**
     * Search performance for dashboard (summary + top queries + top pages).
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getReport(int $days = 30): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Search Console OAuth is not configured. Visit /admin/analytics/gsc-auth to connect, then set GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN in .env.',
                'code' => 'not_configured',
            ];
        }

        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        [$startDate, $endDate] = $this->dateRangeFromDays($days);

        try {
            $summary = $this->querySearchAnalytics([], $startDate, $endDate, 1);
            $summaryRow = $summary['rows'][0] ?? null;

            $queries = $this->querySearchAnalytics(['query'], $startDate, $endDate, 10);
            $pages = $this->querySearchAnalytics(['page'], $startDate, $endDate, 10);

            return [
                'ok' => true,
                'data' => [
                    'site_url' => config('search_console.site_url'),
                    'range' => [
                        'days' => $days,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'label' => "Last {$days} days",
                    ],
                    'summary' => [
                        'clicks' => (int) ($summaryRow['clicks'] ?? 0),
                        'impressions' => (int) ($summaryRow['impressions'] ?? 0),
                        'ctr_percent' => round(($summaryRow['ctr'] ?? 0) * 100, 2),
                        'position' => round($summaryRow['position'] ?? 0, 1),
                    ],
                    'top_queries' => $this->mapRows($queries['rows'] ?? []),
                    'top_pages' => $this->mapRows($pages['rows'] ?? []),
                ],
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $this->friendlyError($e),
                'code' => 'exception',
            ];
        }
    }

    /**
     * @return array{0: string, 1: string} YYYY-MM-DD
     */
    protected function dateRangeFromDays(int $days): array
    {
        $end = now()->subDays(3)->startOfDay();
        $start = $end->copy()->subDays($days - 1);

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    protected function createOAuthClient(): GoogleClient
    {
        $client = new GoogleClient;
        $client->setAuthConfig(config('search_console.oauth_client_json'));
        $client->setRedirectUri(config('search_console.redirect_uri'));
        $client->setScopes([SearchConsole::WEBMASTERS_READONLY]);
        $client->setAccessType('offline');

        return $client;
    }

    protected function client(): SearchConsole
    {
        if ($this->service === null) {
            $googleClient = $this->createOAuthClient();
            $googleClient->fetchAccessTokenWithRefreshToken(config('search_console.refresh_token'));
            $this->service = new SearchConsole($googleClient);
        }

        return $this->service;
    }

    /**
     * @param  array<int, string>  $dimensions
     * @return array{rows: array<int, array{clicks: float, impressions: float, ctr: float, position: float, keys: array<int, string>}>}
     */
    protected function querySearchAnalytics(array $dimensions, string $startDate, string $endDate, int $rowLimit): array
    {
        $request = new SearchAnalyticsQueryRequest;
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setRowLimit($rowLimit);

        if ($dimensions !== []) {
            $request->setDimensions($dimensions);
        }

        $response = $this->client()->searchanalytics->query(
            config('search_console.site_url'),
            $request,
        );

        $rows = [];
        foreach ($response->getRows() ?? [] as $row) {
            $keys = $row->getKeys() ?? [];
            $rows[] = [
                'keys' => $keys,
                'clicks' => (float) $row->getClicks(),
                'impressions' => (float) $row->getImpressions(),
                'ctr' => (float) $row->getCtr(),
                'position' => (float) $row->getPosition(),
            ];
        }

        usort($rows, fn ($a, $b) => $b['clicks'] <=> $a['clicks']);

        return ['rows' => $rows];
    }

    /**
     * @param  array<int, array{keys: array<int, string>, clicks: float, impressions: float, ctr: float, position: float}>  $rows
     * @return array<int, array{label: string, clicks: int, impressions: int, ctr_percent: float, position: float}>
     */
    protected function mapRows(array $rows): array
    {
        $mapped = [];
        foreach ($rows as $row) {
            $label = $row['keys'][0] ?? '—';
            $mapped[] = [
                'label' => $label,
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
                'ctr_percent' => round($row['ctr'] * 100, 2),
                'position' => round($row['position'], 1),
            ];
        }

        return $mapped;
    }

    protected function friendlyError(Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, '403') || str_contains(strtolower($message), 'forbidden')) {
            return $message . ' — Sign in with a Google account that has access to this Search Console property.';
        }

        if (str_contains($message, 'invalid_grant') || str_contains(strtolower($message), 'token')) {
            return $message . ' — Re-authorize at /admin/analytics/gsc-auth and update GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN.';
        }

        if (str_contains($message, '404') || str_contains(strtolower($message), 'not found')) {
            return $message . ' — Check GOOGLE_SEARCH_CONSOLE_SITE_URL matches your property exactly.';
        }

        return $message;
    }

    /**
     * Indexed pages from Search Console sitemaps.list (sum of indexed counts per sitemap).
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string|int}
     */
    public function getIndexedPagesFromSitemaps(): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Search Console OAuth is not configured.',
                'code' => 'not_configured',
            ];
        }

        try {
            $siteUrl = config('search_console.site_url');
            $response = $this->client()->sitemaps->listSitemaps($siteUrl);

            $indexedTotal = 0;
            $submittedTotal = 0;
            $sitemaps = [];

            foreach ($response->getSitemap() ?? [] as $sitemap) {
                $path = $sitemap->getPath() ?? '';
                $sitemapIndexed = 0;
                $sitemapSubmitted = 0;

                foreach ($sitemap->getContents() ?? [] as $content) {
                    $sitemapIndexed += (int) ($content->getIndexed() ?? 0);
                    $sitemapSubmitted += (int) ($content->getSubmitted() ?? 0);
                }

                $indexedTotal += $sitemapIndexed;
                $submittedTotal += $sitemapSubmitted;

                $sitemaps[] = [
                    'path' => $path,
                    'indexed' => $sitemapIndexed,
                    'submitted' => $sitemapSubmitted,
                    'last_downloaded' => $sitemap->getLastDownloaded(),
                ];
            }

            return [
                'ok' => true,
                'data' => [
                    'site_url' => $siteUrl,
                    'indexed_pages' => $indexedTotal,
                    'submitted_pages' => $submittedTotal,
                    'sitemaps' => $sitemaps,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $this->friendlyError($e),
                'code' => 'exception',
            ];
        }
    }
}
