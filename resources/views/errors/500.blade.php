@extends('layouts.app')

@section('title', '500 - Server Error | Hot Tub Buyer')

@section('content')
<div class="container" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 20px;">
    <div style="max-width: 600px;">
        <div style="font-size: 120px; font-weight: 800; color: #ef4444; line-height: 1; margin-bottom: 20px; opacity: 0.2;">500</div>
        <h1 style="font-size: 32px; margin-bottom: 16px; color: #111827;">Internal Server Error</h1>
        <p style="font-size: 18px; color: #6b7280; margin-bottom: 32px; line-height: 1.6;">
            Something went wrong on our side. We're working on fixing it. Please try again later or contact support if the problem persists.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="{{ url('/') }}" class="btn btn--primary btn--lg" style="padding: 14px 32px; border-radius: 999px;">Back to Home</a>
            <button onclick="window.location.reload()" class="btn btn--outline btn--lg" style="padding: 14px 32px; border-radius: 999px;">Try Again</button>
        </div>
    </div>
</div>
@endsection
