{{-- SEO Performance — Google Search Console (additive partial) --}}
@php
    $sc = $searchConsoleResult['data'] ?? [];
    $summary = $sc['summary'] ?? [];
    $scRange = $sc['range'] ?? [];
@endphp

<section class="analytics-dash__section" style="margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--gray-200,#e5e7eb);">
    <h2 class="analytics-dash__section-title">SEO Performance</h2>
    <p class="text-sm text-muted" style="margin:-0.5rem 0 1rem;">
        Google Search Console — {{ $sc['site_url'] ?? config('search_console.site_url') }}
        @if(!empty($scRange['label']))
            · {{ $scRange['label'] }} ({{ $scRange['start_date'] }} → {{ $scRange['end_date'] }})
        @endif
    </p>

    <div class="panel-stats-grid" style="margin-bottom:1.25rem;">
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Total clicks</div>
            <div class="panel-stat-card__value">{{ number_format($summary['clicks'] ?? 0) }}</div>
        </div>
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Total impressions</div>
            <div class="panel-stat-card__value">{{ number_format($summary['impressions'] ?? 0) }}</div>
        </div>
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Average CTR</div>
            <div class="panel-stat-card__value">{{ number_format($summary['ctr_percent'] ?? 0, 2) }}%</div>
        </div>
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Average position</div>
            <div class="panel-stat-card__value">{{ number_format($summary['position'] ?? 0, 1) }}</div>
        </div>
    </div>

    <div class="analytics-dash__grid-2">
        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Top 10 search queries</div>
            <div class="table-responsive">
                <table class="table analytics-table" id="scQueriesTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Query</th>
                            <th data-sort="num">Clicks</th>
                            <th data-sort="num">Impressions</th>
                            <th data-sort="num">CTR</th>
                            <th data-sort="num">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sc['top_queries'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td data-value="{{ $row['clicks'] }}">{{ number_format($row['clicks']) }}</td>
                            <td data-value="{{ $row['impressions'] }}">{{ number_format($row['impressions']) }}</td>
                            <td data-value="{{ $row['ctr_percent'] }}">{{ number_format($row['ctr_percent'], 2) }}%</td>
                            <td data-value="{{ $row['position'] }}">{{ number_format($row['position'], 1) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-muted text-center">No query data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Top 10 landing pages (Google)</div>
            <div class="table-responsive">
                <table class="table analytics-table" id="scPagesTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Page</th>
                            <th data-sort="num">Clicks</th>
                            <th data-sort="num">Impressions</th>
                            <th data-sort="num">CTR</th>
                            <th data-sort="num">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sc['top_pages'] ?? [] as $row)
                        <tr>
                            <td><code style="font-size:0.75rem;word-break:break-all;">{{ $row['label'] }}</code></td>
                            <td data-value="{{ $row['clicks'] }}">{{ number_format($row['clicks']) }}</td>
                            <td data-value="{{ $row['impressions'] }}">{{ number_format($row['impressions']) }}</td>
                            <td data-value="{{ $row['ctr_percent'] }}">{{ number_format($row['ctr_percent'], 2) }}%</td>
                            <td data-value="{{ $row['position'] }}">{{ number_format($row['position'], 1) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-muted text-center">No landing page data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
