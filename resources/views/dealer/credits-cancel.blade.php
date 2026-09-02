@extends('layouts.dealer')
@section('title', __('panel.credits_cancel.title').' – '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.credits_cancel.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.credits_cancel.sub') }}</p>
    </div>
</div>

<div class="card" style="max-width: 500px; margin: 2rem auto; text-align: center; padding: 3rem;">
    <div style="background: #fee2e2; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
        <svg width="32" height="32" fill="none" stroke="#ef4444" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M18 6L6 18M6 6l12 12"></path>
        </svg>
    </div>
    <h2 class="fw-800 mb-2" style="font-size: 1.5rem; color: var(--gray-900)">{{ __('panel.credits_cancel.failed') }}</h2>
    <p class="text-muted mb-4">{{ __('panel.credits_cancel.body') }}</p>
    
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="{{ route('dealer.credits') }}" class="btn btn--primary">{{ __('panel.credits_cancel.retry') }}</a>
        <a href="{{ route('dealer.overview') }}" class="btn btn--ghost">{{ __('panel.credits_cancel.close') }}</a>
    </div>
</div>
@endsection
