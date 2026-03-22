@extends('layouts.admin')
@section('title', 'Payment Processor – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Payment Processor Configuration</h1><p class="panel-page-sub">Choose how dealers purchase credits – automated or manual</p></div>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif

<form method="POST" action="{{ route('admin.pricing-processor.save') }}">
    @csrf
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Credit Purchase Method & Mode</div>
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Active Processor</label>
                <div style="display:flex;gap:15px;margin-top:5px">
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="radio" name="active_processor" value="manual" @checked(optional($settings)->active_processor==='manual' || !optional($settings)->active_processor)> Manual
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="radio" name="active_processor" value="paypal" @checked(optional($settings)->active_processor==='paypal')> PayPal
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="radio" name="active_processor" value="stripe" @checked(optional($settings)->active_processor==='stripe')> Stripe
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mode</label>
                <div style="display:flex;gap:15px;margin-top:5px">
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="radio" name="mode" value="test" @checked(optional($settings)->mode==='test' || !optional($settings)->mode)> Test (Sandbox)
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="radio" name="mode" value="live" @checked(optional($settings)->mode==='live')> Live (Production)
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">PayPal API Configuration</div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">PayPal Client ID</label><input class="form-input" name="paypal_client_id" value="{{ old('paypal_client_id', optional($settings)->paypal_client_id) }}"></div>
            <div class="form-group"><label class="form-label">PayPal Secret Key</label><input class="form-input" name="paypal_secret" value="{{ old('paypal_secret', optional($settings)->paypal_secret) }}"></div>
        </div>
    </div>
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Stripe API Configuration</div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Stripe Publishable Key</label><input class="form-input" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', optional($settings)->stripe_publishable_key) }}"></div>
            <div class="form-group"><label class="form-label">Stripe Secret Key</label><input class="form-input" name="stripe_secret_key" value="{{ old('stripe_secret_key', optional($settings)->stripe_secret_key) }}"></div>
        </div>
        <div class="grid grid--1 mt-2">
            <div class="form-group"><label class="form-label">Stripe Webhook Secret</label><input class="form-input" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', optional($settings)->stripe_webhook_secret) }}"></div>
        </div>
    </div>
    <div class="modal-actions" style="justify-content:flex-start">
        <button class="btn btn--primary">Save Payment Processor Settings</button>
    </div>
</form>
@endsection
