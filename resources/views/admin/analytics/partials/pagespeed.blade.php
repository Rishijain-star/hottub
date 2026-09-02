{{-- Technical Performance — PageSpeed Insights (additive partial) --}}
@php
    $ps = $pageSpeedResult['data'] ?? [];
    $testUrl = $ps['url'] ?? config('pagespeed.url');
    $strategies = [
        'mobile' => $ps['mobile'] ?? null,
        'desktop' => $ps['desktop'] ?? null,
    ];
    $vitalKeys = ['lcp', 'fcp', 'cls', 'tbt', 'fid', 'ttfb', 'speed_index'];
@endphp

<section class="analytics-dash__section pagespeed-section" style="margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--gray-200,#e5e7eb);">
    <h2 class="analytics-dash__section-title">Technical Performance</h2>
    <p class="text-sm text-muted" style="margin:-0.5rem 0 1rem;">
        Google PageSpeed Insights — <a href="{{ $testUrl }}" target="_blank" rel="noopener">{{ $testUrl }}</a>
        @if(!empty($ps['fetched_at']))
            · Lab data cached · {{ $ps['fetched_at'] }}
        @endif
    </p>

    @if(!empty($ps['note']))
        <p class="text-sm text-muted" style="margin:0 0 1rem;">{{ $ps['note'] }}</p>
    @endif

    @if(!($pageSpeedResult['ok'] ?? false))
        <div class="alert alert--danger">
            <strong>Unable to load PageSpeed data.</strong><br>
            {{ $pageSpeedResult['error'] ?? 'Unknown error.' }}
            <p class="text-sm" style="margin:0.5rem 0 0;">Analysis can take up to 2 minutes. Optional: set <code>PAGESPEED_API_KEY</code> in <code>.env</code> for higher quota.</p>
        </div>
    @else
        <div class="pagespeed-legend text-sm" style="margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:1rem;">
            <span><span class="pagespeed-pill pagespeed-pill--good">Good</span></span>
            <span><span class="pagespeed-pill pagespeed-pill--needs-improvement">Needs improvement</span></span>
            <span><span class="pagespeed-pill pagespeed-pill--poor">Poor</span></span>
        </div>

        <div class="analytics-dash__grid-2">
            @foreach($strategies as $strategyKey => $strategy)
                @if(empty($strategy))
                    @continue
                @endif
                @php
                    $score = $strategy['performance_score'] ?? null;
                    $scoreRating = $strategy['performance_rating'] ?? 'unknown';
                    $vitals = $strategy['vitals'] ?? [];
                @endphp
                <div class="card" style="padding:1.25rem;">
                    <div class="fw-800" style="font-size:1rem;margin-bottom:1rem;text-transform:capitalize;">{{ $strategyKey }}</div>

                    <div class="pagespeed-score pagespeed-score--{{ $scoreRating }}" style="margin-bottom:1.25rem;">
                        <div class="pagespeed-score__label">Performance score</div>
                        <div class="pagespeed-score__value">{{ $score !== null ? $score : '—' }}</div>
                    </div>

                    <div class="fw-700 text-sm" style="margin-bottom:0.5rem;">Core Web Vitals (lab)</div>
                    <div class="pagespeed-vitals">
                        @foreach($vitalKeys as $key)
                            @php $metric = $vitals[$key] ?? null; @endphp
                            @if(empty($metric))
                                @continue
                            @endif
                            @php $rating = $metric['rating'] ?? 'unknown'; @endphp
                            <div class="pagespeed-vital pagespeed-vital--{{ $rating }}">
                                <span class="pagespeed-vital__label">{{ $metric['label'] }}</span>
                                <span class="pagespeed-vital__value">{{ $metric['display'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<style>
.pagespeed-pill { display:inline-block; padding:0.15rem 0.5rem; border-radius:4px; font-size:0.75rem; font-weight:600; }
.pagespeed-pill--good { background:#dcfce7; color:#166534; }
.pagespeed-pill--needs-improvement { background:#ffedd5; color:#9a3412; }
.pagespeed-pill--poor { background:#fee2e2; color:#991b1b; }
.pagespeed-score { padding:1rem; border-radius:8px; text-align:center; border:2px solid transparent; }
.pagespeed-score__label { font-size:0.8rem; opacity:0.9; margin-bottom:0.25rem; }
.pagespeed-score__value { font-size:2.5rem; font-weight:800; line-height:1; }
.pagespeed-score--good { background:#dcfce7; border-color:#86efac; color:#166534; }
.pagespeed-score--needs-improvement { background:#ffedd5; border-color:#fdba74; color:#9a3412; }
.pagespeed-score--poor { background:#fee2e2; border-color:#fca5a5; color:#991b1b; }
.pagespeed-score--unknown { background:#f1f5f9; border-color:#e2e8f0; color:#475569; }
.pagespeed-vitals { display:flex; flex-direction:column; gap:0.5rem; }
.pagespeed-vital { display:flex; justify-content:space-between; align-items:center; padding:0.5rem 0.75rem; border-radius:6px; border-left:4px solid transparent; font-size:0.875rem; }
.pagespeed-vital--good { background:#f0fdf4; border-left-color:#22c55e; }
.pagespeed-vital--needs-improvement { background:#fff7ed; border-left-color:#f97316; }
.pagespeed-vital--poor { background:#fef2f2; border-left-color:#ef4444; }
.pagespeed-vital--unknown { background:#f8fafc; border-left-color:#94a3b8; }
.pagespeed-vital__label { font-weight:600; }
.pagespeed-vital__value { font-variant-numeric:tabular-nums; }
</style>
