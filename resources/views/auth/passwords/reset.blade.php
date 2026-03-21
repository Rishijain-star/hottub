@extends('layouts.app')
@section('title', 'Reset Password – Hot Tub Buyer')
@section('content')

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 15V17M12 7V13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="auth-card__title">New Password</h1>
        <p class="auth-card__sub">Create a strong new password</p>

        <form class="auth-form" method="POST" action="{{ route('password.update.new') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input
                    class="form-input auth-input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    autofocus
                >
                @error('password')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input
                    class="form-input auth-input"
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="••••••••"
                    required
                >
            </div>

            @if(session('error'))
                <div class="alert alert--danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
            @endif

            <button type="submit" class="auth-submit-btn">
                Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
