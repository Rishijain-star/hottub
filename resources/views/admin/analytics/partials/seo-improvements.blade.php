{{-- SEO improvements (additive — after SEO Performance section) --}}
<div class="analytics-dash__subsection" style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px dashed var(--gray-200,#e5e7eb);">
    <h3 class="fw-800" style="font-size:0.95rem;margin:0 0 1rem;">SEO improvements</h3>

    @if(!($gscIndexedPages['ok'] ?? false))
        <div class="alert alert--warning text-sm">{{ $gscIndexedPages['error'] ?? 'Indexed pages data unavailable.' }}</div>
    @else
        @php $idx = $gscIndexedPages['data'] ?? []; @endphp
        <div class="panel-stats-grid" style="margin-bottom:1rem;">
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Indexed pages (sitemaps)</div>
                <div class="panel-stat-card__value">{{ number_format($idx['indexed_pages'] ?? 0) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Submitted URLs (sitemaps)</div>
                <div class="panel-stat-card__value">{{ number_format($idx['submitted_pages'] ?? 0) }}</div>
            </div>
        </div>
        @if(!empty($idx['sitemaps']))
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Sitemaps</div>
                <table class="table analytics-table">
                    <thead><tr><th>Sitemap</th><th>Indexed</th><th>Submitted</th></tr></thead>
                    <tbody>
                        @foreach($idx['sitemaps'] as $sm)
                        <tr>
                            <td><code style="font-size:0.75rem;word-break:break-all;">{{ $sm['path'] }}</code></td>
                            <td>{{ number_format($sm['indexed'] ?? 0) }}</td>
                            <td>{{ number_format($sm['submitted'] ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="text-sm text-muted" style="margin:0.75rem 0 0;">Source: Search Console <code>sitemaps.list</code> for {{ $idx['site_url'] ?? config('search_console.site_url') }}</p>
    @endif
</div>
