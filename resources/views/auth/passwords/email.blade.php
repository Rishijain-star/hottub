@extends('layouts.app')
@section('title', 'Forgot Password – Hot Tub Buyer')
@section('content')

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>

        <h1 class="auth-card__title">Forgot Password</h1>
        <p class="auth-card__sub">Enter your email to receive an OTP</p>

        <form class="auth-form" method="POST" action="{{ route('password.otp.send') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    class="form-input auth-input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
                @error('email')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>

            @if(session('error'))
                <div class="alert alert--danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
            @endif

            <button type="submit" class="auth-submit-btn">
                Send OTP
            </button>
        </form>

        <p class="auth-card__footer-link">
            Remember your password? <a href="/login">Back to Login</a>
        </p>
    </div>
</div>
@endsection
