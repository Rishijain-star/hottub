{{-- Technical improvements (additive) --}}
<div class="analytics-dash__subsection" style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px dashed var(--gray-200,#e5e7eb);">
    <h3 class="fw-800" style="font-size:0.95rem;margin:0 0 1rem;">Technical improvements</h3>

    <div class="analytics-dash__grid-2" style="margin-bottom:1.25rem;">
        <div class="card" style="padding:1rem;">
            <div class="fw-800" style="margin-bottom:0.75rem;">Mobile usability (accessibility score)</div>
            @if(!($pageSpeedAccessibility['ok'] ?? false))
                <p class="text-sm text-muted">{{ $pageSpeedAccessibility['error'] ?? 'Accessibility scores unavailable.' }}</p>
            @else
                @php $acc = $pageSpeedAccessibility['data'] ?? []; @endphp
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    @foreach(['mobile', 'desktop'] as $strat)
                        @php
                            $s = $acc[$strat] ?? [];
                            $score = $s['score'] ?? null;
                            $rating = $s['rating'] ?? 'unknown';
                        @endphp
                        <div class="pagespeed-score pagespeed-score--{{ $rating }}" style="flex:1;min-width:120px;padding:0.75rem;">
                            <div class="pagespeed-score__label" style="font-size:0.75rem;">{{ ucfirst($strat) }}</div>
                            <div class="pagespeed-score__value" style="font-size:1.75rem;">{{ $score ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
                @if(!empty($acc['note']))
                    <p class="text-sm text-muted" style="margin:0.5rem 0 0;">{{ $acc['note'] }}</p>
                @endif
            @endif
        </div>
        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Browser compatibility</div>
            @if(!($browserCompatibility['ok'] ?? false))
                <p class="text-sm text-muted" style="padding:1rem;">{{ $browserCompatibility['error'] ?? 'Browser data unavailable.' }}</p>
            @else
                <table class="table analytics-table">
                    <thead><tr><th>Browser</th><th>Sessions</th></tr></thead>
                    <tbody>
                        @forelse($browserCompatibility['data']['browsers'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['browser'] }}</td>
                            <td>{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center">No browser data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card" style="padding:0;">
        <div class="fw-800" style="padding:1rem 1rem 0;">Broken links / 404 pages</div>
        <p class="text-sm text-muted" style="padding:0 1rem;margin:0;">GA4 pages where path or title contains <code>404</code></p>
        @if(!($brokenLinks404['ok'] ?? false))
            <p class="text-sm text-muted" style="padding:1rem;">{{ $brokenLinks404['error'] ?? '404 data unavailable.' }}</p>
        @else
            <table class="table analytics-table">
                <thead><tr><th>Path</th><th>Title</th><th>Views</th><th>Sessions</th></tr></thead>
                <tbody>
                    @forelse($brokenLinks404['data']['pages'] ?? [] as $row)
                    <tr>
                        <td><code style="font-size:0.75rem;">{{ $row['page_path'] }}</code></td>
                        <td>{{ Str::limit($row['page_title'], 40) }}</td>
                        <td>{{ number_format($row['views']) }}</td>
                        <td>{{ number_format($row['sessions']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center">No 404-related page hits in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
