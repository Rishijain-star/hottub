@extends('layouts.app')
@section('title', 'Success – Hot Tub Buyer')
@section('content')

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__icon" style="background: #10b981;">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="auth-card__title">Success!</h1>
        <p class="auth-card__sub">Password reset successful</p>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="/login" class="auth-submit-btn" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In Now
            </a>
        </div>
    </div>
</div>
@endsection
