@extends('layouts.app')
@section('title', 'Security check – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <h1 class="auth-card__title">Security verification</h1>
        <p class="auth-card__sub">Your details are saved and security check passed. Click below to send a one-time code to <strong>{{ $phone }}</strong>.</p>

        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('register.send.otp') }}" class="auth-form" id="registerSecurityForm">
            @csrf
            <x-image-captcha />
            <x-otp-antibot />
            <button type="submit" class="auth-submit-btn" id="registerSecurityBtn">Send OTP</button>
        </form>

        <p class="auth-card__footer-link mt-3">
            <a href="{{ route('register', ['restart' => 1]) }}">Edit registration details</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const f = document.getElementById('registerSecurityForm');
    const b = document.getElementById('registerSecurityBtn');
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
});
</script>
<x-csrf-keepalive />
@endsection
