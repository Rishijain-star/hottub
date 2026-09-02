@extends('layouts.manufacturer')
@section('title', __('panel.account.pending_title').' - '.__('panel.manufacturer_title'))
@section('content')
<div class="card" style="max-width:560px;margin:2rem auto;text-align:center;padding:3rem 2rem">
    <div style="width:72px;height:72px;background:#fef3c7;color:#d97706;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem">⏳</div>
    <h1 class="panel-page-title" style="margin-bottom:.5rem">{{ __('panel.account.under_review') }}</h1>
    <p class="text-muted" style="line-height:1.6;margin-bottom:1.5rem">{{ __('panel.account.under_review_sub') }}</p>
    <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="btn btn--ghost">{{ __('panel.account.sign_out') }}</button></form>
</div>
@endsection
