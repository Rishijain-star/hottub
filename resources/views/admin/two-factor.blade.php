@extends('layouts.app')
@section('title', 'Admin verification – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <h1 class="auth-card__title">Two-step verification</h1>
        @if(!empty($missingPhone))
            <p class="auth-card__sub">This admin account has no mobile number. Add a phone number to the user record (e.g. in Admin → User management), sign out, and sign in again.</p>
            <p class="auth-card__footer-link"><a href="{{ route('logout') }}">Sign out</a></p>
        @else
            @if(!empty($needsSend))
                <p class="auth-card__sub">We will send a 6-digit code by SMS to <strong>{{ $phone }}</strong></p>
            @else
                <p class="auth-card__sub">Enter the 6-digit code we sent by SMS to <strong>{{ $phone }}</strong></p>
            @endif
            @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
            @if(!empty($devOtp))
                <div class="alert alert--success">
                    Dev mode (SMS provider not configured): <strong>{{ $devOtp }}</strong>
                </div>
            @endif
            @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
            @if(!empty($needsSend))
            <form method="POST" action="{{ route('admin.two-factor.send') }}" class="auth-form" id="adminTwoFactorSendForm">
                @csrf
                <x-otp-antibot />
                <button type="submit" class="auth-submit-btn" id="adminTwoFactorSendBtn">Send verification code</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="auth-form" id="adminTwoFactorForm">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="code">Verification code</label>
                    <input class="form-input auth-input" type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="000000">
                </div>
                <button type="submit" class="auth-submit-btn" id="adminTwoFactorBtn">Verify &amp; continue</button>
            </form>
            <form method="POST" action="{{ route('admin.two-factor.resend') }}" class="mt-3" id="adminTwoFactorResendForm">
                @csrf
                <x-otp-antibot />
                <button type="submit" class="btn btn--ghost btn--full" id="adminTwoFactorResendBtn">Resend code</button>
            </form>
            @endif
            <p class="auth-card__footer-link mt-3"><a href="{{ route('logout') }}">Sign out</a></p>
        @endif
    </div>
</div>
@if(empty($missingPhone))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sf = document.getElementById('adminTwoFactorSendForm');
    const sb = document.getElementById('adminTwoFactorSendBtn');
    let sendBusy = false;
    if (sf && sb) {
        sf.addEventListener('submit', function (e) {
            if (sendBusy) { e.preventDefault(); return; }
            sendBusy = true;
            sb.disabled = true;
        });
    }
    const f = document.getElementById('adminTwoFactorForm');
    const b = document.getElementById('adminTwoFactorBtn');
    let busy = false;
    if (f && b) {
        f.addEventListener('submit', function (e) {
            if (busy) { e.preventDefault(); return; }
            busy = true;
            b.disabled = true;
        });
    }
    const rf = document.getElementById('adminTwoFactorResendForm');
    const rb = document.getElementById('adminTwoFactorResendBtn');
    let resendBusy = false;
    if (rf && rb) {
        rf.addEventListener('submit', function (e) {
            if (resendBusy) { e.preventDefault(); return; }
            resendBusy = true;
            rb.disabled = true;
        });
    }
});
</script>
@endif
@endsection
