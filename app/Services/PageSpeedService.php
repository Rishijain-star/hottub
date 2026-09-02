<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Google PageSpeed Insights API — performance score and Core Web Vitals (lab).
 */
class PageSpeedService
{
    /** @var array<string, array{good: float, needs_improvement: float}> */
    protected array $thresholdsMs = [
        'largest-contentful-paint' => ['good' => 2500, 'needs_improvement' => 4000],
        'first-contentful-paint' => ['good' => 1800, 'needs_improvement' => 3000],
        'total-blocking-time' => ['good' => 200, 'needs_improvement' => 600],
        'max-potential-fid' => ['good' => 100, 'needs_improvement' => 300],
        'server-response-time' => ['good' => 800, 'needs_improvement' => 1800],
        'speed-index' => ['good' => 3400, 'needs_improvement' => 5800],
    ];

    protected float $clsGood = 0.1;

    protected float $clsNeedsImprovement = 0.25;

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string}
     */
    public function getReport(): array
    {
        $url = (string) config('pagespeed.url');
        if (! filled($url)) {
            return [
                'ok' => false,
                'error' => 'PAGESPEED_URL is not configured.',
                'code' => 'not_configured',
            ];
        }

        $cacheKey = 'pagespeed.report.'.md5($url);
        $ttl = (int) config('pagespeed.cache_ttl', 3600);

        try {
            $data = Cache::remember($cacheKey, $ttl, fn () => $this->fetchBothStrategies($url));

            return [
                'ok' => true,
                'data' => $data,
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
     * @return array<string, mixed>
     */
    protected function fetchBothStrategies(string $url): array
    {
        $mobile = $this->fetchStrategy($url, 'mobile');
        $desktop = $this->fetchStrategy($url, 'desktop');

        return [
            'url' => $url,
            'fetched_at' => now()->toIso8601String(),
            'cache_ttl_seconds' => (int) config('pagespeed.cache_ttl', 3600),
            'note' => 'Lab data from Lighthouse via PageSpeed Insights API. Scores may differ from field (CrUX) data.',
            'mobile' => $mobile,
            'desktop' => $desktop,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchStrategy(string $url, string $strategy): array
    {
        $params = [
            'url' => $url,
            'strategy' => $strategy,
            'category' => 'performance',
        ];

        if (filled(config('pagespeed.api_key'))) {
            $params['key'] = config('pagespeed.api_key');
        }

        $response = Http::timeout(120)
            ->acceptJson()
            ->get((string) config('pagespeed.api_endpoint'), $params);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException(
                'PageSpeed API ('.$strategy.') failed: HTTP '.$response->status().' — '.$message
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('PageSpeed API ('.$strategy.') returned an invalid response.');
        }

        return $this->parseLighthouseResult($json, $strategy);
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    protected function parseLighthouseResult(array $json, string $strategy): array
    {
        $lighthouse = $json['lighthouseResult'] ?? [];
        $audits = is_array($lighthouse['audits'] ?? null) ? $lighthouse['audits'] : [];
        $scoreRaw = $lighthouse['categories']['performance']['score'] ?? null;
        $performanceScore = $scoreRaw !== null ? (int) round((float) $scoreRaw * 100) : null;

        $fidAudit = isset($audits['max-potential-fid'])
            ? $this->auditMetric($audits, 'max-potential-fid', 'Max Potential FID (lab)')
            : null;

        $vitals = [
            'lcp' => $this->auditMetric($audits, 'largest-contentful-paint', 'LCP'),
            'fcp' => $this->auditMetric($audits, 'first-contentful-paint', 'FCP'),
            'cls' => $this->auditMetric($audits, 'cumulative-layout-shift', 'CLS', isCls: true),
            'tbt' => $this->auditMetric($audits, 'total-blocking-time', 'TBT'),
            'fid' => $fidAudit,
            'ttfb' => $this->auditMetric($audits, 'server-response-time', 'TTFB'),
            'speed_index' => $this->auditMetric($audits, 'speed-index', 'Speed Index'),
        ];

        return [
            'strategy' => $strategy,
            'performance_score' => $performanceScore,
            'performance_rating' => $this->performanceScoreRating($performanceScore),
            'vitals' => $vitals,
        ];
    }

    /**
     * @param  array<string, mixed>  $audits
     * @return array{label: string, display: string, value: ?float, unit: string, rating: string}
     */
    protected function auditMetric(
        array $audits,
        string $id,
        string $label,
        bool $isCls = false,
    ): array {
        $audit = is_array($audits[$id] ?? null) ? $audits[$id] : [];
        $numeric = isset($audit['numericValue']) ? (float) $audit['numericValue'] : null;
        $display = (string) ($audit['displayValue'] ?? '—');

        return [
            'label' => $label,
            'display' => $display !== '' ? $display : '—',
            'value' => $numeric,
            'unit' => $isCls ? '' : 'ms',
            'rating' => $isCls
                ? $this->clsRating($numeric)
                : $this->msRating($id, $numeric),
        ];
    }

    protected function performanceScoreRating(?int $score): string
    {
        if ($score === null) {
            return 'unknown';
        }
        if ($score >= 90) {
            return 'good';
        }
        if ($score >= 50) {
            return 'needs-improvement';
        }

        return 'poor';
    }

    protected function msRating(string $auditId, ?float $valueMs): string
    {
        if ($valueMs === null) {
            return 'unknown';
        }

        $thresholds = $this->thresholdsMs[$auditId] ?? null;
        if ($thresholds === null) {
            return 'unknown';
        }

        if ($valueMs <= $thresholds['good']) {
            return 'good';
        }
        if ($valueMs <= $thresholds['needs_improvement']) {
            return 'needs-improvement';
        }

        return 'poor';
    }

    protected function clsRating(?float $value): string
    {
        if ($value === null) {
            return 'unknown';
        }
        if ($value <= $this->clsGood) {
            return 'good';
        }
        if ($value <= $this->clsNeedsImprovement) {
            return 'needs-improvement';
        }

        return 'poor';
    }

    /**
     * Mobile usability proxy: Lighthouse accessibility scores (mobile + desktop).
     *
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, code?: string}
     */
    public function getAccessibilityScores(): array
    {
        $url = (string) config('pagespeed.url');
        if (! filled($url)) {
            return [
                'ok' => false,
                'error' => 'PAGESPEED_URL is not configured.',
                'code' => 'not_configured',
            ];
        }

        $cacheKey = 'pagespeed.accessibility.'.md5($url);
        $ttl = (int) config('pagespeed.cache_ttl', 3600);

        try {
            $data = Cache::remember($cacheKey, $ttl, fn () => [
                'url' => $url,
                'mobile' => $this->fetchCategoryScore($url, 'mobile', 'accessibility'),
                'desktop' => $this->fetchCategoryScore($url, 'desktop', 'accessibility'),
                'note' => 'Accessibility score from Lighthouse (PageSpeed Insights). Higher is better (0–100).',
            ]);

            return [
                'ok' => true,
                'data' => $data,
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
     * @return array{strategy: string, score: ?int, rating: string}
     */
    protected function fetchCategoryScore(string $url, string $strategy, string $category): array
    {
        $params = [
            'url' => $url,
            'strategy' => $strategy,
            'category' => $category,
        ];

        if (filled(config('pagespeed.api_key'))) {
            $params['key'] = config('pagespeed.api_key');
        }

        $response = Http::timeout(120)
            ->acceptJson()
            ->get((string) config('pagespeed.api_endpoint'), $params);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException(
                'PageSpeed API ('.$strategy.'/'.$category.') failed: HTTP '.$response->status().' — '.$message
            );
        }

        $json = $response->json();
        $scoreRaw = $json['lighthouseResult']['categories'][$category]['score'] ?? null;
        $score = $scoreRaw !== null ? (int) round((float) $scoreRaw * 100) : null;

        return [
            'strategy' => $strategy,
            'score' => $score,
            'rating' => $this->performanceScoreRating($score),
        ];
    }
}
