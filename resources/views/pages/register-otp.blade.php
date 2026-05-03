@extends('layouts.app')
@section('title', 'Verify OTP – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 3v4m0 10v4m9-9h-4M7 12H3"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
        </div>
        <h1 class="auth-card__title">Verify OTP</h1>
        <p class="auth-card__sub">Enter the 6-digit code sent to <strong>{{ $phone }}</strong></p>

        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
        @if(!empty($devOtp))
            <div class="alert alert--success">Dev OTP: <strong>{{ $devOtp }}</strong></div>
        @endif

        <form method="POST" action="{{ route('register.otp.verify') }}" class="auth-form" id="registerOtpVerifyForm">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">
            <div class="form-group">
                <label class="form-label" for="code">OTP Verification Code *</label>
                <input class="form-input auth-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="Enter 6-digit OTP">
            </div>
            <button type="submit" class="auth-submit-btn" id="registerOtpVerifyBtn">Verify OTP &amp; Create Account</button>
        </form>

        <form method="POST" action="{{ route('register.otp.resend') }}" class="mt-3" id="registerOtpResendForm">
            @csrf
            <button type="submit" class="btn btn--ghost btn--full" id="registerOtpResendBtn">Resend OTP</button>
        </form>

        <p class="auth-card__footer-link mt-3">
            Need to edit details? <a href="{{ route('register') }}">Start again</a>
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
@endsection
