@extends('layouts.app')

@section('title', '403 - Access Denied | Hot Tub Buyer')

@section('content')
<div class="container" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 20px;">
    <div style="max-width: 600px;">
        <div style="font-size: 120px; font-weight: 800; color: #f59e0b; line-height: 1; margin-bottom: 20px; opacity: 0.2;">403</div>
        <h1 style="font-size: 32px; margin-bottom: 16px; color: #111827;">Access Denied</h1>
        <p style="font-size: 18px; color: #6b7280; margin-bottom: 32px; line-height: 1.6;">
            You do not have permission to access this page. If you believe this is an error, please contact your administrator.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="{{ url('/') }}" class="btn btn--primary btn--lg" style="padding: 14px 32px; border-radius: 999px;">Back to Home</a>
            <a href="{{ route('dashboard') }}" class="btn btn--outline btn--lg" style="padding: 14px 32px; border-radius: 999px;">Go to Dashboard</a>
        </div>
    </div>
</div>
@endsection
