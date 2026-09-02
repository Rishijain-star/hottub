@extends('layouts.admin')
@section('title', __('panel.admin.pages.analytics_index.title') . ' – Admin Panel')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/analytics-dashboard.css') }}">
@endsection

@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.analytics_index.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.analytics_index.sub') }} {{ config('analytics.property_id') }}</p>
    </div>
    <div class="analytics-dash__filters">
        <span class="text-sm text-muted" style="margin-right:0.25rem;">{{ __('panel.admin.pages.analytics_index.range') }}</span>
        @foreach([7 => __('panel.admin.pages.analytics_index.days_7'), 30 => __('panel.admin.pages.analytics_index.days_30'), 90 => __('panel.admin.pages.analytics_index.days_90')] as $d => $label)
            <a href="{{ route('admin.analytics.index', ['days' => $d]) }}"
               class="btn btn--ghost btn--sm {{ $days === $d ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.analytics.test') }}" class="btn btn--ghost btn--sm">{{ __('panel.admin.pages.analytics_index.api_test') }}</a>
    </div>
</div>

@if(!$result['ok'])
    <div class="alert alert--danger">
        <strong>Unable to load analytics.</strong><br>
        {{ $result['error'] ?? 'Unknown error.' }}
    </div>
@else
    @php
        $ta = $result['data']['traffic_audience'];
        $ts = $result['data']['traffic_sources'];
        $cp = $result['data']['content_performance'];
        $range = $result['data']['range'];
    @endphp

    <div class="alert alert--success" style="margin-bottom:1.25rem;">
        Showing data for <strong>{{ $range['label'] }}</strong> ({{ $range['start_date'] }} → {{ $range['end_date'] }})
    </div>

    {{-- SECTION 1: Traffic & Audience --}}
    <section class="analytics-dash__section">
        <h2 class="analytics-dash__section-title">Traffic &amp; Audience</h2>
        <div class="panel-stats-grid" style="margin-bottom:1.25rem;">
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Total users</div>
                <div class="panel-stat-card__value">{{ number_format($ta['total_users']) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Sessions</div>
                <div class="panel-stat-card__value">{{ number_format($ta['sessions']) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">New visitors</div>
                <div class="panel-stat-card__value">{{ number_format($ta['new_users']) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Returning visitors</div>
                <div class="panel-stat-card__value">{{ number_format($ta['returning_users']) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Pages / session</div>
                <div class="panel-stat-card__value">{{ number_format($ta['pages_per_session'], 2) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Avg engagement</div>
                <div class="panel-stat-card__value">{{ gmdate('i\ms\s', $ta['avg_engagement_time_seconds']) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Engagement rate</div>
                <div class="panel-stat-card__value">{{ $ta['engagement_rate_percent'] }}%</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Bounce rate</div>
                <div class="panel-stat-card__value">{{ $ta['bounce_rate_percent'] }}%</div>
            </div>
        </div>

        <div class="analytics-dash__grid-2">
            <div class="card analytics-dash__chart-wrap">
                <div class="fw-800 mb-3">Device split</div>
                <canvas id="deviceChart" height="220"></canvas>
            </div>
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Top UK regions / cities</div>
                <table class="table analytics-table" id="geoTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Location</th>
                            <th data-sort="num">Sessions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ta['geography'] as $row)
                        <tr>
                            <td>{{ $row['location'] }}</td>
                            <td data-value="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center">No UK geography data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- SECTION 2: Traffic Sources --}}
    <section class="analytics-dash__section">
        <h2 class="analytics-dash__section-title">Traffic Sources</h2>
        <div class="analytics-dash__grid-2">
            <div class="card analytics-dash__chart-wrap">
                <div class="fw-800 mb-3">Channel breakdown</div>
                <canvas id="channelChart" height="220"></canvas>
            </div>
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Top referring websites</div>
                <table class="table analytics-table" id="referrersTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Source</th>
                            <th data-sort="num">Sessions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ts['top_referrers'] as $row)
                        <tr>
                            <td>{{ $row['source'] }}</td>
                            <td data-value="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center">No referrer data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card" style="padding:0;margin-top:1rem;">
            <table class="table analytics-table" id="channelsTable">
                <thead>
                    <tr>
                        <th data-sort="text">Channel</th>
                        <th data-sort="num">Sessions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $channelLabels = [
                            'organic' => 'Organic search',
                            'direct' => 'Direct',
                            'social' => 'Social',
                            'referral' => 'Referral',
                            'paid' => 'Paid',
                            'other' => 'Other',
                        ];
                    @endphp
                    @foreach($channelLabels as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td data-value="{{ $ts['channels'][$key] ?? 0 }}">{{ number_format($ts['channels'][$key] ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- SECTION 3: Content Performance --}}
    <section class="analytics-dash__section">
        <h2 class="analytics-dash__section-title">Content Performance</h2>
        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Most visited pages (top 20)</div>
            <div class="table-responsive">
                <table class="table analytics-table" id="pagesTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Page</th>
                            <th data-sort="num">Views</th>
                            <th data-sort="num">Avg time on page</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cp['most_visited'] as $row)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $row['page'] }}</code></td>
                            <td data-value="{{ $row['views'] }}">{{ number_format($row['views']) }}</td>
                            <td data-value="{{ $row['avg_time_on_page_seconds'] }}">{{ gmdate('i\ms\s', $row['avg_time_on_page_seconds']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="analytics-dash__grid-2" style="margin-top:1.25rem;">
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Entry pages</div>
                <table class="table analytics-table" id="entryTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Landing page</th>
                            <th data-sort="num">Sessions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cp['entry_pages'] as $row)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $row['page'] }}</code></td>
                            <td data-value="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card" style="padding:0;">
                <div class="fw-800" style="padding:1rem 1rem 0;">Exit pages</div>
                <table class="table analytics-table" id="exitTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Page</th>
                            <th data-sort="num">Exits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cp['exit_pages'] as $row)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $row['page'] }}</code></td>
                            <td data-value="{{ $row['exits'] }}">{{ number_format($row['exits']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('admin.analytics.partials.content-improvements', ['contentImprovements' => $contentImprovements ?? ['ok' => false]])
    </section>
@endif

@if(!($searchConsoleResult['ok'] ?? false))
    <div class="alert alert--danger" style="margin-top:2rem;">
        <strong>Unable to load Search Console data.</strong><br>
        {{ $searchConsoleResult['error'] ?? 'Unknown error.' }}
        <p class="text-sm" style="margin:0.75rem 0 0;">
            <a href="{{ route('admin.analytics.gsc-auth') }}" class="btn btn--primary btn--sm">Connect Search Console (OAuth)</a>
        </p>
    </div>
@else
    @include('admin.analytics.partials.search-console', ['searchConsoleResult' => $searchConsoleResult])
@endif

@include('admin.analytics.partials.seo-improvements', ['gscIndexedPages' => $gscIndexedPages ?? ['ok' => false]])

@include('admin.analytics.partials.user-behaviour', ['clarityResult' => $clarityResult ?? ['ok' => false, 'error' => 'Clarity data not loaded.']])
@include('admin.analytics.partials.user-behaviour-improvements', [
    'returningVisitorFrequency' => $returningVisitorFrequency ?? ['ok' => false],
    'popularFilterCategories' => $popularFilterCategories ?? ['ok' => false],
])

@include('admin.analytics.partials.pagespeed', ['pageSpeedResult' => $pageSpeedResult ?? ['ok' => false, 'error' => 'PageSpeed data not loaded.']])
@include('admin.analytics.partials.technical-improvements', [
    'pageSpeedAccessibility' => $pageSpeedAccessibility ?? ['ok' => false],
    'browserCompatibility' => $browserCompatibility ?? ['ok' => false],
    'brokenLinks404' => $brokenLinks404 ?? ['ok' => false],
])
@endsection

@section('scripts')
@if($result['ok'] ?? false)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ta = @json($ta);
    const ts = @json($ts);

    const deviceCtx = document.getElementById('deviceChart');
    if (deviceCtx) {
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Mobile', 'Desktop', 'Tablet', 'Other'],
                datasets: [{
                    data: [
                        ta.devices.mobile || 0,
                        ta.devices.desktop || 0,
                        ta.devices.tablet || 0,
                        ta.devices.other || 0,
                    ],
                    backgroundColor: ['#0ea5a3', '#6366f1', '#f59e0b', '#94a3b8'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    const channelCtx = document.getElementById('channelChart');
    if (channelCtx) {
        const ch = ts.channels;
        new Chart(channelCtx, {
            type: 'bar',
            data: {
                labels: ['Organic', 'Direct', 'Social', 'Referral', 'Paid', 'Other'],
                datasets: [{
                    label: 'Sessions',
                    data: [ch.organic, ch.direct, ch.social, ch.referral, ch.paid, ch.other],
                    backgroundColor: '#0ea5a3',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } },
            },
        });
    }

    document.querySelectorAll('.analytics-table').forEach(function (table) {
        table.querySelectorAll('th[data-sort]').forEach(function (th, colIndex) {
            th.addEventListener('click', function () {
                const tbody = table.querySelector('tbody');
                const type = th.getAttribute('data-sort');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const asc = th.dataset.order !== 'asc';
                th.dataset.order = asc ? 'asc' : 'desc';

                rows.sort(function (a, b) {
                    const ac = a.children[colIndex];
                    const bc = b.children[colIndex];
                    let av = type === 'num' ? parseFloat(ac.getAttribute('data-value') || ac.textContent.replace(/,/g, '')) : ac.textContent.trim();
                    let bv = type === 'num' ? parseFloat(bc.getAttribute('data-value') || bc.textContent.replace(/,/g, '')) : bc.textContent.trim();
                    if (type === 'num') {
                        return asc ? av - bv : bv - av;
                    }
                    return asc ? av.localeCompare(bv) : bv.localeCompare(av);
                });

                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        });
    });
})();
</script>
@endif
@if($searchConsoleResult['ok'] ?? false)
<script>
(function () {
    document.querySelectorAll('#scQueriesTable, #scPagesTable').forEach(function (table) {
        table.querySelectorAll('th[data-sort]').forEach(function (th, colIndex) {
            th.addEventListener('click', function () {
                const tbody = table.querySelector('tbody');
                const type = th.getAttribute('data-sort');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const asc = th.dataset.order !== 'asc';
                th.dataset.order = asc ? 'asc' : 'desc';
                rows.sort(function (a, b) {
                    const ac = a.children[colIndex];
                    const bc = b.children[colIndex];
                    let av = type === 'num' ? parseFloat(ac.getAttribute('data-value') || '0') : ac.textContent.trim();
                    let bv = type === 'num' ? parseFloat(bc.getAttribute('data-value') || '0') : bc.textContent.trim();
                    if (type === 'num') return asc ? av - bv : bv - av;
                    return asc ? av.localeCompare(bv) : bv.localeCompare(av);
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        });
    });
})();
</script>
@endif
@endsection
