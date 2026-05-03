@extends('layouts.app')
@section('title', 'Verify phone – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <h1 class="auth-card__title">Verify your phone</h1>
        <p class="auth-card__sub">We sent a 6-digit code to <strong>{{ $phone }}</strong></p>
        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if(!empty($devOtp))
            <div class="alert alert--success">
                Dev mode OTP (Twilio not configured): <strong>{{ $devOtp }}</strong>
            </div>
        @endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('verify.phone.submit') }}" class="auth-form" id="verifyPhoneForm">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">Verification code</label>
                <input class="form-input auth-input" type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="000000">
            </div>
            <button type="submit" class="auth-submit-btn" id="verifyPhoneBtn">Verify &amp; continue</button>
        </form>
        <form method="POST" action="{{ route('verify.phone.resend') }}" class="mt-3" id="verifyPhoneResendForm">
            @csrf
            <button type="submit" class="btn btn--ghost btn--full" id="verifyPhoneResendBtn">Resend code</button>
        </form>
        {{-- GET logout avoids 419 when session/CSRF token is stale after OTP errors (route allows GET). --}}
        <p class="auth-card__footer-link mt-3"><a href="{{ route('logout') }}">Sign out</a></p>
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
