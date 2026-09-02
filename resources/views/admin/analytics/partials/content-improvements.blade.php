{{-- Content improvements (additive) --}}
<div class="analytics-dash__subsection" style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px dashed var(--gray-200,#e5e7eb);">
    <h3 class="fw-800" style="font-size:0.95rem;margin:0 0 1rem;">Content improvements</h3>

    @if(!($contentImprovements['ok'] ?? false))
        <div class="alert alert--warning text-sm">{{ $contentImprovements['error'] ?? 'Content improvement data unavailable.' }}</div>
    @else
        @php $ci = $contentImprovements['data'] ?? []; @endphp
        @if(isset($ci['scroll_depth_percent']) && $ci['scroll_depth_percent'] !== null)
            <div class="panel-stat-card" style="margin-bottom:1rem;max-width:280px;">
                <div class="panel-stat-card__label">Scroll depth (scrolled users %)</div>
                <div class="panel-stat-card__value">{{ number_format($ci['scroll_depth_percent'], 1) }}%</div>
            </div>
        @endif

        <div class="analytics-dash__grid-2">
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Top product / model pages</div>
                <p class="text-sm text-muted" style="padding:0 1rem;margin:0;">Paths containing <code>/hot-tubs/</code> or <code>/swim-spas/</code></p>
                <table class="table analytics-table">
                    <thead>
                        <tr><th>Page</th><th>Views</th><th>Sessions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($ci['top_product_pages'] ?? [] as $row)
                        <tr>
                            <td><code style="font-size:0.75rem;">{{ $row['page'] }}</code></td>
                            <td>{{ number_format($row['views']) }}</td>
                            <td>{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center">No product page data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Internal site search terms</div>
                <table class="table analytics-table">
                    <thead>
                        <tr><th>Search term</th><th>Sessions</th><th>Events</th></tr>
                    </thead>
                    <tbody>
                        @forelse($ci['site_search_terms'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['term'] }}</td>
                            <td>{{ number_format($row['sessions']) }}</td>
                            <td>{{ number_format($row['events']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center">No site search data (enable Site Search in GA4).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
