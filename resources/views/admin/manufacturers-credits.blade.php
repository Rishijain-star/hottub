@extends('layouts.admin')
@section('title', __('panel.admin.pages.manufacturers_credits.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.pages.manufacturers_credits.title') }}</h1><p class="panel-page-sub">{{ $manufacturer->name }} ({{ __('panel.admin.pages.manufacturers_credits.current') }}: {{ $manufacturer->credits }})</p></div>
    <a href="{{ route('admin.manufacturers') }}" class="btn">{{ __('panel.admin.common.back') }}</a>
</div>
<div class="card">
    <form method="POST" action="{{ route('admin.manufacturers.credits.add', $manufacturer) }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Amount *</label>
            <input class="form-input" type="number" name="amount" min="1" required>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary">Add Credits</button>
        </div>
    </form>
</div>
@endsection
