<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Microsoft Clarity Data Export API + dashboard URLs.
 */
class MicrosoftClarityService
{
    /** @var array<int, string> */
    protected array $dimensionKeys = [
        'Browser', 'Device', 'Country/Region', 'OS', 'Source', 'Medium',
        'Campaign', 'Channel', 'URL', 'Page Title', 'Referrer URL',
    ];

    public function isConfigured(): bool
    {
        return filled(config('clarity.project_id')) && filled(config('clarity.api_token'));
    }

    public function projectId(): string
    {
        return (string) config('clarity.project_id');
    }

    public function dashboardUrl(string $section = 'dashboard'): string
    {
        $base = rtrim((string) config('clarity.dashboard_base'), '/');
        $id = $this->projectId();

        return match ($section) {
            'recordings' => "{$base}/{$id}/recordings",
            'heatmaps' => "{$base}/{$id}/heatmaps",
            default => "{$base}/{$id}/dashboard",
        };
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string}
     */
    public function getReport(int $days = 30): array
    {
        $projectId = $this->projectId();
        $links = [
            'dashboard' => $this->dashboardUrl('dashboard'),
            'recordings' => $this->dashboardUrl('recordings'),
            'heatmaps' => $this->dashboardUrl('heatmaps'),
        ];

        if (! filled($projectId)) {
            return [
                'ok' => false,
                'error' => 'CLARITY_PROJECT_ID is not set in .env.',
                'code' => 'not_configured',
                'data' => ['links' => $links],
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'Clarity API token missing. In Clarity → Settings → Data Export, generate a token and set CLARITY_API_TOKEN in .env.',
                'code' => 'no_api_token',
                'data' => [
                    'links' => $links,
                    'project_id' => $projectId,
                    'api_note' => $this->apiWindowNote($days),
                ],
            ];
        }

        $numOfDays = $this->apiNumOfDays($days);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withToken((string) config('clarity.api_token'))
                ->get((string) config('clarity.api_endpoint'), [
                    'numOfDays' => (string) $numOfDays,
                ]);

            if ($response->status() === 429) {
                return [
                    'ok' => false,
                    'error' => 'Clarity API daily limit reached (10 requests per project per day). Try again tomorrow or use the dashboard links below.',
                    'code' => 'rate_limit',
                    'data' => ['links' => $links, 'project_id' => $projectId],
                ];
            }

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Clarity API error ('.$response->status().'): '.($response->json('message') ?? $response->body()),
                    'code' => 'api_error',
                    'data' => ['links' => $links, 'project_id' => $projectId],
                ];
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return [
                    'ok' => false,
                    'error' => 'Unexpected Clarity API response format.',
                    'code' => 'invalid_response',
                    'data' => ['links' => $links, 'project_id' => $projectId],
                ];
            }

            $metrics = $this->parseLiveInsights($payload);

            return [
                'ok' => true,
                'data' => [
                    'links' => $links,
                    'project_id' => $projectId,
                    'range' => [
                        'dashboard_days' => $days,
                        'api_num_of_days' => $numOfDays,
                        'label' => "Last {$numOfDays} day".($numOfDays > 1 ? 's' : '').' (API maximum)',
                        'note' => $this->apiWindowNote($days),
                    ],
                    'metrics' => $metrics,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => 'exception',
                'data' => ['links' => $links, 'project_id' => $projectId],
            ];
        }
    }

    /**
     * Clarity API only supports 1–3 days lookback (not 7/30/90).
     */
    protected function apiNumOfDays(int $dashboardDays): int
    {
        if ($dashboardDays <= 1) {
            return 1;
        }
        if ($dashboardDays <= 2) {
            return 2;
        }

        return 3;
    }

    protected function apiWindowNote(int $dashboardDays): string
    {
        if ($dashboardDays > 3) {
            return 'Clarity Data Export API only provides the last 1–3 days of data (not '.$dashboardDays.' days). Metrics below use the maximum 3-day window.';
        }

        return 'Clarity Data Export API provides the last 1–3 days of data.';
    }

    /**
     * @param  array<int, mixed>  $payload
     * @return array{total_sessions: int, dead_clicks: int, rage_clicks: int, scroll_depth_avg: ?float}
     */
    protected function parseLiveInsights(array $payload): array
    {
        $totalSessions = 0;
        $deadClicks = 0;
        $rageClicks = 0;
        $scrollDepths = [];

        foreach ($payload as $block) {
            if (! is_array($block)) {
                continue;
            }

            $metricName = strtolower((string) ($block['metricName'] ?? ''));
            $rows = $block['information'] ?? [];
            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                if (str_contains($metricName, 'traffic')) {
                    $totalSessions += (int) ($row['totalSessionCount'] ?? 0);
                }

                if (str_contains($metricName, 'dead click')) {
                    $deadClicks += $this->extractCountFromRow($row);
                }

                if (str_contains($metricName, 'rage click')) {
                    $rageClicks += $this->extractCountFromRow($row);
                }

                if (str_contains($metricName, 'scroll depth')) {
                    $depth = $this->extractScrollDepthFromRow($row);
                    if ($depth !== null) {
                        $scrollDepths[] = $depth;
                    }
                }
            }
        }

        return [
            'total_sessions' => $totalSessions,
            'dead_clicks' => $deadClicks,
            'rage_clicks' => $rageClicks,
            'scroll_depth_avg' => $scrollDepths !== []
                ? round(array_sum($scrollDepths) / count($scrollDepths), 1)
                : null,
        ];
    }

    protected function extractCountFromRow(array $row): int
    {
        $sum = 0;
        foreach ($row as $key => $value) {
            if ($this->isDimensionField((string) $key)) {
                continue;
            }
            $keyLower = strtolower((string) $key);
            if (
                is_numeric($value)
                && (str_contains($keyLower, 'click') || str_contains($keyLower, 'count') || $keyLower === 'value')
            ) {
                $sum += (int) $value;
            }
        }

        return $sum;
    }

    protected function extractScrollDepthFromRow(array $row): ?float
    {
        foreach ($row as $key => $value) {
            if ($this->isDimensionField((string) $key) || ! is_numeric($value)) {
                continue;
            }
            $keyLower = strtolower((string) $key);
            if (str_contains($keyLower, 'scroll') || str_contains($keyLower, 'depth') || str_contains($keyLower, 'percent')) {
                return (float) $value;
            }
        }

        foreach ($row as $key => $value) {
            if (! $this->isDimensionField((string) $key) && is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    protected function isDimensionField(string $key): bool
    {
        foreach ($this->dimensionKeys as $dimension) {
            if (strcasecmp($key, $dimension) === 0) {
                return true;
            }
        }

        return false;
    }
}
