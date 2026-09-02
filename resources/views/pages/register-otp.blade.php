@extends('layouts.app')
@section('title', __('pages.auth.otp_title'))
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 3v4m0 10v4m9-9h-4M7 12H3"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
        </div>
        <h1 class="auth-card__title">{{ __('pages.auth.otp_heading') }}</h1>
        <p class="auth-card__sub">{{ __('pages.register_otp.subheading', ['phone' => $phone]) }}</p>

        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
        @if(!empty($devOtp))
            <div class="alert alert--success">{{ __('pages.register_otp.dev_otp') }}: <strong>{{ $devOtp }}</strong></div>
        @endif

        <form method="POST" action="{{ route('register.otp.verify') }}" class="auth-form" id="registerOtpVerifyForm">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">{{ __('pages.register_otp.verification_code') }}</label>
                <input class="form-input auth-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="{{ __('pages.register_otp.code_placeholder') }}">
            </div>
            <button type="submit" class="auth-submit-btn" id="registerOtpVerifyBtn">{{ __('pages.auth.verify_otp_create') }}</button>
        </form>

        <form method="POST" action="{{ route('register.otp.resend') }}" class="mt-3" id="registerOtpResendForm">
            @csrf
            <x-otp-antibot />
            <button type="submit" class="btn btn--ghost btn--full" id="registerOtpResendBtn">{{ __('pages.register_otp.resend_otp') }}</button>
        </form>

        <p class="auth-card__footer-link mt-3">
            {{ __('pages.register_otp.edit_details') }} <a href="{{ route('register', ['restart' => 1]) }}">{{ __('pages.register_otp.start_again') }}</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const verifyForm = document.getElementById('registerOtpVerifyForm');
    const verifyBtn = document.getElementById('registerOtpVerifyBtn');
    let verifyBusy = false;
    if (verifyForm && verifyBtn) {
        verifyForm.addEventListener('submit', function (e) {
            if (verifyBusy) {
                e.preventDefault();
                return;
            }
            verifyBusy = true;
            verifyBtn.disabled = true;
        });
    }

    const resendForm = document.getElementById('registerOtpResendForm');
    const resendBtn = document.getElementById('registerOtpResendBtn');
    let resendBusy = false;
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function (e) {
            if (resendBusy) {
                e.preventDefault();
                return;
            }
            resendBusy = true;
            resendBtn.disabled = true;
        });
    }
});
</script>
<x-csrf-keepalive />
@endsection
