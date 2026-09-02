@extends('layouts.admin')
@section('title', __('panel.admin.pages.analytics_test.title') . ' – Admin Panel')

@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.analytics_test.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.analytics_test.sub') }}</p>
    </div>
    <a href="{{ route('admin.analytics.test', ['format' => 'json']) }}" class="btn btn--ghost" target="_blank" rel="noopener">{{ __('panel.admin.pages.analytics_test.view_json') }}</a>
</div>

@if(!$result['ok'])
    <div class="alert alert--danger">
        <strong>Unable to load analytics data.</strong><br>
        {{ $result['error'] ?? 'Unknown error.' }}
        @if(($result['code'] ?? '') === 'not_configured')
            <p class="text-sm" style="margin-top:0.75rem;margin-bottom:0">
                Check <code>GOOGLE_ANALYTICS_PROPERTY_ID</code> and <code>GOOGLE_APPLICATION_CREDENTIALS</code> in <code>.env</code>.
            </p>
        @endif
    </div>
@else
    @php $d = $result['data']; @endphp
    <div class="alert alert--success">Connected to GA4 property <strong>{{ $d['property_id'] }}</strong> ({{ $d['start_date'] }} → {{ $d['end_date'] }})</div>

    <div class="panel-stats-grid">
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Active users</div>
            <div class="panel-stat-card__value">{{ number_format($d['active_users']) }}</div>
        </div>
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Sessions</div>
            <div class="panel-stat-card__value">{{ number_format($d['sessions']) }}</div>
        </div>
        <div class="panel-stat-card">
            <div class="panel-stat-card__label">Page views</div>
            <div class="panel-stat-card__value">{{ number_format($d['page_views']) }}</div>
        </div>
    </div>
@endif

<div class="card" style="padding:1.25rem;margin-top:1rem;">
    <p class="text-sm text-muted" style="margin:0">
        Full dashboard (charts, Search Console, engagement events) will be added in the next step.
        Search Console requires separate API access and is not included in this test.
    </p>
</div>
@endsection
