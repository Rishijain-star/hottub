@extends('layouts.app')
@section('title', 'Session expired – Hot Tub Buyer')
@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <h1 class="auth-card__title">Session expired</h1>
        <p class="auth-card__sub" style="margin-bottom:1.25rem;">
            This page was open too long or your browser refreshed the security token. Please try again.
        </p>
        <div class="alert alert--danger" style="margin-bottom:1.25rem;">
            Your form was not submitted. Go back and try once more.
        </div>
        <a href="{{ route('register') }}" class="auth-submit-btn" style="display:inline-flex;text-decoration:none;justify-content:center;width:100%;">Back to registration</a>
        <p class="auth-card__footer-link mt-3">
            <a href="{{ route('login') }}">Already have an account? Log in</a>
        </p>
    </div>
</div>
@endsection
