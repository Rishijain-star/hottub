@extends('layouts.app')
@section('title', 'Verify your email – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M4 4h16v16H4z"/>
                <path d="M4 8l8 5 8-5"/>
            </svg>
        </div>
        <h1 class="auth-card__title">Verify your email</h1>
        <p class="auth-card__sub">Enter the 6-digit code we sent to <strong>{{ $email }}</strong></p>

        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
        @if(!empty($devOtp))
            <div class="alert alert--success">Dev mode code: <strong>{{ $devOtp }}</strong></div>
        @endif

        <form method="POST" action="{{ route('register.email.otp.verify') }}" class="auth-form" id="registerEmailOtpVerifyForm">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">Verification code</label>
                <input class="form-input auth-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="000000">
            </div>
            <button type="submit" class="auth-submit-btn" id="registerEmailOtpVerifyBtn">Verify email</button>
        </form>

        <form method="POST" action="{{ route('register.email.otp.resend') }}" class="mt-3" id="registerEmailOtpResendForm">
            @csrf
            <x-otp-antibot />
            <button type="submit" class="btn btn--ghost btn--full" id="registerEmailOtpResendBtn" @if($resendSeconds > 0) disabled @endif>
                Resend code @if($resendSeconds > 0)(<span id="resendCountdown">{{ $resendSeconds }}</span>s)@endif
            </button>
        </form>

        <p class="auth-card__footer-link mt-3">
            <a href="{{ route('register', ['restart' => 1]) }}">Edit registration details</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const verifyForm = document.getElementById('registerEmailOtpVerifyForm');
    const verifyBtn = document.getElementById('registerEmailOtpVerifyBtn');
    let verifyBusy = false;
    if (verifyForm && verifyBtn) {
        verifyForm.addEventListener('submit', function (e) {
            if (verifyBusy) { e.preventDefault(); return; }
            verifyBusy = true;
            verifyBtn.disabled = true;
        });
    }

    const resendForm = document.getElementById('registerEmailOtpResendForm');
    const resendBtn = document.getElementById('registerEmailOtpResendBtn');
    let resendBusy = false;
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function (e) {
            if (resendBusy) { e.preventDefault(); return; }
            resendBusy = true;
            resendBtn.disabled = true;
        });
    }

    const countdownEl = document.getElementById('resendCountdown');
    if (countdownEl && resendBtn) {
        let remaining = parseInt(countdownEl.textContent, 10) || 0;
        const timer = setInterval(function () {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend code';
                return;
            }
            countdownEl.textContent = String(remaining);
        }, 1000);
    }
});
</script>
<x-csrf-keepalive />
@endsection
