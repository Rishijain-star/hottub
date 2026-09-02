@extends('layouts.app')
@section('title', __('pages.auth.otp_title'))
@section('content')

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
            </svg>
        </div>

        <h1 class="auth-card__title">{{ __('pages.auth.otp_heading') }}</h1>
        <p class="auth-card__sub">{{ __('pages.auth.otp_sub', ['email' => session('reset_email')]) }}</p>

        <form class="auth-form" method="POST" action="{{ route('password.otp.verify') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="otp">{{ __('pages.auth.otp_code') }}</label>
                <input
                    class="form-input auth-input"
                    type="text"
                    id="otp"
                    name="otp"
                    placeholder="123456"
                    maxlength="6"
                    required
                    autofocus
                    style="text-align: center; letter-spacing: 0.5rem; font-size: 1.5rem;"
                >
                @error('otp')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>

            @if(session('error'))
                <div class="alert alert--danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
            @endif

            <button type="submit" class="auth-submit-btn">
                {{ __('pages.auth.otp_verify_btn') }}
            </button>
        </form>

        <p class="auth-card__footer-link">
            {{ __('pages.auth.otp_retry') }} <a href="{{ route('password.request') }}">{{ __('pages.auth.otp_try_again') }}</a>
        </p>
    </div>
</div>
@endsection
