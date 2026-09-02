@extends('layouts.admin')
@section('title', __('panel.admin.pages.analytics_gsc_auth.title') . ' – Admin Panel')

@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.analytics_gsc_auth.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.analytics_gsc_auth.sub') }}</p>
    </div>
    <a href="{{ route('admin.analytics.index') }}" class="btn btn--ghost">{{ __('panel.admin.pages.analytics_gsc_auth.back_to_analytics') }}</a>
</div>

@if(!empty($error))
    <div class="alert alert--danger">
        <strong>Authorization failed.</strong><br>{{ $error }}
    </div>
@endif

@if(!empty($success) && !empty($refreshToken))
    <div class="alert alert--success">
        <strong>Authorization successful.</strong> Copy the refresh token below into your <code>.env</code> file, then run <code>php artisan config:clear</code>.
    </div>
    <div class="card" style="padding:1.25rem;">
        <label class="form-label fw-700">Add to .env</label>
        <pre style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:1rem;overflow:auto;font-size:0.85rem;white-space:pre-wrap;word-break:break-all;">GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN={{ $refreshToken }}</pre>
        <p class="text-sm text-muted" style="margin:1rem 0 0;">Do not commit this token to git. Reload <a href="{{ route('admin.analytics.index') }}">Analytics dashboard</a> after saving.</p>
    </div>
@else
    <div class="card" style="padding:1.25rem;">
        <h2 class="fw-800" style="font-size:1rem;margin:0 0 1rem;">Before you start</h2>
        <ol class="text-sm" style="margin:0;padding-left:1.25rem;line-height:1.7;">
            <li>In <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console → Credentials</a>, open your OAuth 2.0 Client ID.</li>
            <li>Add this <strong>Authorized redirect URI</strong> (must match exactly):<br>
                <code style="display:block;margin:0.5rem 0;padding:0.5rem;background:#f1f5f9;border-radius:6px;">{{ $redirectUri ?? config('search_console.redirect_uri') }}</code>
            </li>
            <li>Sign in with <strong>marketing@hottubbuyer.co.uk</strong> (or any account with full Search Console access to your property).</li>
            <li>Click below to start OAuth — you will be redirected to Google, then back here with a refresh token.</li>
        </ol>
        <a href="{{ route('admin.analytics.gsc-auth') }}" class="btn btn--primary" style="margin-top:1.25rem;">Connect with Google</a>
    </div>
@endif
@endsection
