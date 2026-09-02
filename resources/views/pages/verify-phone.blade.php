@extends('layouts.app')
@section('title', __('pages.verify_phone.page_title'))
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <h1 class="auth-card__title">{{ __('pages.verify_phone.heading') }}</h1>
        <p class="auth-card__sub">{{ __('pages.verify_phone.subheading', ['phone' => $phone]) }}</p>
        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if(!empty($devOtp))
            <div class="alert alert--success">
                {{ __('pages.verify_phone.dev_mode_otp') }}: <strong>{{ $devOtp }}</strong>
            </div>
        @endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('verify.phone.submit') }}" class="auth-form" id="verifyPhoneForm">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">{{ __('pages.verify_phone.verification_code') }}</label>
                <input class="form-input auth-input" type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="{{ __('pages.verify_phone.code_placeholder') }}">
            </div>
            <button type="submit" class="auth-submit-btn" id="verifyPhoneBtn">{{ __('pages.verify_phone.verify_continue') }}</button>
        </form>
        <form method="POST" action="{{ route('verify.phone.resend') }}" class="mt-3" id="verifyPhoneResendForm">
            @csrf
            <x-otp-antibot />
            <button type="submit" class="btn btn--ghost btn--full" id="verifyPhoneResendBtn">{{ __('pages.verify_phone.resend_code') }}</button>
        </form>
        {{-- GET logout avoids 419 when session/CSRF token is stale after OTP errors (route allows GET). --}}
        <p class="auth-card__footer-link mt-3"><a href="{{ route('logout') }}">{{ __('pages.verify_phone.sign_out') }}</a></p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const f = document.getElementById('verifyPhoneForm');
    const b = document.getElementById('verifyPhoneBtn');
    let busy = false;
    if (f && b) {
        f.addEventListener('submit', function (e) {
            if (busy) {
                e.preventDefault();
                return;
            }
            busy = true;
            b.disabled = true;
        });
    }
    const rf = document.getElementById('verifyPhoneResendForm');
    const rb = document.getElementById('verifyPhoneResendBtn');
    let resendBusy = false;
    if (rf && rb) {
        rf.addEventListener('submit', function (e) {
            if (resendBusy) {
                e.preventDefault();
                return;
            }
            resendBusy = true;
            rb.disabled = true;
        });
    }
});
</script>
@endsection
