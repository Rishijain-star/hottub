@extends('layouts.app')
@section('title', __('pages.auth.login_title'))
@section('content')

<div class="auth-page">
    <div class="auth-card">

        {{-- Icon --}}
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
        </div>

        <h1 class="auth-card__title">{{ __('pages.auth.welcome_back') }}</h1>
        <p class="auth-card__sub">{{ __('pages.auth.sign_in') }}</p>

        <form class="auth-form" method="POST" action="/login" onsubmit="return handleLogin(event)">
            @csrf
            @if(request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <div class="form-group">
                <label class="form-label" for="email">{{ __('pages.auth.email') }}</label>
                <input
                    class="form-input auth-input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                    <label class="form-label" for="password" style="margin-bottom:0;">{{ __('pages.auth.password') }}</label>
                    <div class="auth-forgot-links">
                        <a href="{{ route('password.request') }}" class="auth-forgot">{{ __('pages.auth.forgot_password') }}</a>
                    </div>
                </div>
                <div class="auth-pw-wrap">
                    <input
                        class="form-input auth-input"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="auth-pw-toggle" onclick="togglePassword()" tabindex="-1" aria-label="Toggle password visibility">
                        <svg id="eyeShow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeHide" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                @error('password')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>

            @if(session('error'))
                <div class="alert alert--danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
            @endif

            <x-image-captcha />
            <x-otp-antibot />

            <button type="submit" class="auth-submit-btn" id="loginBtn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                {{ __('pages.auth.sign_in_btn') }}
            </button>
            <p style="margin-top: 1.25rem; font-size: 0.85rem; color: #6b7280; text-align: center; line-height: 1.4;">
                Buying a hot tub is exciting. Our platform connects you with trusted dealers who will support you from purchase to installation and long-term ownership.
            </p>
        </form>

        <p class="auth-card__footer-link">
            {{ __('pages.auth.no_account') }} <a href="/register">{{ __('pages.auth.create_one') }}</a>
        </p>

        <div class="auth-card__divider"></div>

        <p class="auth-card__hint">Test accounts available — see documentation for credentials</p>

    </div>
</div>

<script>
const loginI18n = @json([
    'signIn' => __('pages.auth.sign_in_btn'),
    'signingIn' => __('pages.auth.signing_in'),
]);

function togglePassword() {
    const input   = document.getElementById('password');
    const eyeShow = document.getElementById('eyeShow');
    const eyeHide = document.getElementById('eyeHide');
    const isHidden = input.type === 'password';
    input.type     = isHidden ? 'text' : 'password';
    eyeShow.style.display = isHidden ? 'none'  : 'block';
    eyeHide.style.display = isHidden ? 'block' : 'none';
}

function handleLogin(e) {
    var turnstileWrap = document.querySelector('.cf-turnstile-wrap');
    if (turnstileWrap) {
        var token = document.querySelector('input[name="cf-turnstile-response"]');
        if (!token || !token.value) {
            alert('Please wait for the Cloudflare security check to load and complete before signing in.');
            e.preventDefault();
            return false;
        }
    }

    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = `
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        ${loginI18n.signingIn}`;
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = `
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            ${loginI18n.signIn}`;
    }, 8000);
    return true;
}
</script>
<x-csrf-keepalive />
@endsection
