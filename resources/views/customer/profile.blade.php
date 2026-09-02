@extends('layouts.customer')
@section('title', __('panel.nav.profile_settings').' – '.__('panel.customer_title'))
@section('content')
@php $user = auth()->user(); @endphp
<div class="panel-page-header"><div><h1 class="panel-page-title">{{ __('panel.nav.profile_settings') }}</h1><p class="panel-page-sub">{{ __('panel.customer_sub') }}</p></div></div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="grid grid--2" style="display:grid; grid-template-columns: 0.6fr 1.4fr; gap: 2rem; align-items: start;">
    <div class="card" style="text-align: center;">
        <div class="fw-800 mb-4" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.profile.change_picture') }}</div>
        <div style="margin-bottom: 1.5rem;">
            @if($user->profile_picture)
                <img src="{{ \App\Support\PublicMedia::url($user->profile_picture) }}" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #f1f5f9;">
            @else
                <div style="width: 150px; height: 150px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #64748b; font-size: 3rem; font-weight: 800; border: 4px solid #f1f5f9;">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
        </div>
        <form action="{{ route('customer.profile.update-image') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <input type="file" name="image" class="form-input" style="font-size: 0.8rem;" required>
            </div>
            <button type="submit" class="btn btn--primary btn--sm w-100">{{ __('panel.profile.update_profile_picture') }}</button>
        </form>
    </div>

    <div class="card">
        <div class="fw-800 mb-4" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.profile.title') }}</div>
        <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid--2" style="gap: 15px;">
                <div class="form-group">
                    <label class="form-label">{{ __('panel.common.name') }}</label>
                    <input type="text" name="name" class="form-input" value="{{ $user->name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.profile.email_address') }}</label>
                    <input type="email" name="email" class="form-input" value="{{ $user->email }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.profile.mobile_number') }}</label>
                    <input type="text" name="phone" class="form-input" value="{{ $user->phone }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.common.postcode') }}</label>
                    <input type="text" name="postcode" class="form-input" value="{{ $user->postcode }}">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">{{ __('panel.leads.address') }}</label>
                    <textarea name="address" class="form-input" rows="2" placeholder="Street, city, county">{{ $user->address }}</textarea>
                </div>
            </div>
            <div class="mt-4" style="text-align: right;">
                <button type="submit" class="btn btn--primary">{{ __('panel.common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.profile.delete_account') }}</div>
    <p class="text-sm text-muted">{{ __('panel.profile.delete_account_sub') }}</p>
    <div style="text-align: right; margin-top: 1rem;">
        <form method="POST" action="{{ route('customer.profile.delete') }}" onsubmit="event.preventDefault(); showConfirmationModal(this, '{{ __('panel.profile.delete_account') }}?', 'You are about to request permanent account deletion. This will be finalized in 30 days. Are you sure?', 'Yes, Request Deletion');">
            @csrf
            @method('DELETE')
            <button class="btn btn--danger">{{ __('panel.profile.delete_my_account') }}</button>
        </form>
    </div>
</div>
@endsection
