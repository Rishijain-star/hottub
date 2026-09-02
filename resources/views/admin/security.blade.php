@extends('layouts.admin')
@section('title', __('panel.admin.security.title') . ' – ' . __('panel.admin_title'))
@section('content')

@php
    $showChangeForm = $errors->any() || session('open_change_form');
@endphp

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.security.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.security.sub') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

<div class="card mb-4">
    <div class="fw-800 mb-3" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.security.current_details') }}</div>
    <div class="grid grid--2" style="gap:1.25rem;">
        <div>
            <div class="text-sm text-muted" style="margin-bottom:0.35rem;">{{ __('panel.admin.security.current_email') }}</div>
            <div class="fw-700" style="font-size:1rem;color:var(--gray-900);word-break:break-all;">{{ $user->email ?: '—' }}</div>
        </div>
        <div>
            <div class="text-sm text-muted" style="margin-bottom:0.35rem;">{{ __('panel.admin.security.current_mobile') }}</div>
            <div class="fw-700" style="font-size:1rem;color:var(--gray-900);">
                @if($user->phone)
                    {{ str_starts_with((string) $user->phone, '+') ? $user->phone : '+' . ltrim((string) $user->phone, '+') }}
                @else
                    —
                @endif
            </div>
        </div>
    </div>
    <p class="text-sm text-muted" style="margin:1.25rem 0 0;">{{ __('panel.admin.security.password_note') }}</p>

    <div style="margin-top:1.25rem;" id="changeDetailsTriggerWrap" @if($showChangeForm) hidden @endif>
        <button type="button" class="btn btn--primary btn--sm" id="showChangeFormBtn">
            {{ __('panel.admin.security.change_btn') }}
        </button>
    </div>
</div>

<div class="card" id="changeDetailsCard" @if(! $showChangeForm) hidden @endif>
    <div class="fw-800 mb-3" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.security.change_details') }}</div>
    <p class="text-sm text-muted" style="margin:0 0 1.25rem;">{{ __('panel.admin.security.change_hint') }}</p>

    <form method="POST" action="{{ route('admin.security.update') }}" id="adminSecurityForm">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="current_password">{{ __('panel.admin.security.current_password') }} *</label>
            <input
                type="password"
                name="current_password"
                id="current_password"
                class="form-input"
                required
                autocomplete="current-password"
            >
            @error('current_password')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label" for="email">{{ __('panel.admin.security.new_email') }} *</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autocomplete="off"
                >
                @error('email')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">{{ __('panel.admin.security.new_mobile') }} *</label>
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    class="form-input"
                    value="{{ old('phone') }}"
                    placeholder="+447..."
                    required
                    autocomplete="off"
                >
                @error('phone')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label" for="password">{{ __('panel.admin.security.new_password') }} *</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-input"
                    required
                    autocomplete="new-password"
                    minlength="6"
                >
                @error('password')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">{{ __('panel.admin.security.confirm_password') }} *</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-input"
                    required
                    autocomplete="new-password"
                    minlength="6"
                >
            </div>
        </div>

        <div class="modal-actions" style="justify-content:flex-start;margin-top:0.5rem;gap:0.5rem;">
            <button type="submit" class="btn btn--primary" id="adminSecuritySubmitBtn">{{ __('panel.admin.security.update_btn') }}</button>
            <button type="button" class="btn btn--ghost" id="cancelChangeFormBtn">{{ __('panel.admin.security.cancel_btn') }}</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggerWrap = document.getElementById('changeDetailsTriggerWrap');
    const changeCard = document.getElementById('changeDetailsCard');
    const showBtn = document.getElementById('showChangeFormBtn');
    const cancelBtn = document.getElementById('cancelChangeFormBtn');
    const form = document.getElementById('adminSecurityForm');
    const submitBtn = document.getElementById('adminSecuritySubmitBtn');
    let busy = false;

    function openChangeForm() {
        if (triggerWrap) triggerWrap.hidden = true;
        if (changeCard) changeCard.hidden = false;
        const first = document.getElementById('current_password');
        if (first) first.focus();
    }

    function closeChangeForm() {
        if (changeCard) changeCard.hidden = true;
        if (triggerWrap) triggerWrap.hidden = false;
        if (form) form.reset();
    }

    if (showBtn) {
        showBtn.addEventListener('click', openChangeForm);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeChangeForm);
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function (e) {
            if (busy) {
                e.preventDefault();
                return;
            }
            busy = true;
            submitBtn.disabled = true;
        });
    }
});
</script>

@endsection
