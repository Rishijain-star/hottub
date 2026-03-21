@extends('layouts.customer')
@section('title', 'Profile Settings – Customer Panel')
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">Profile Settings</h1><p class="panel-page-sub">Manage your account details</p></div></div>
<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Account Information</div>
    <div class="grid grid--2">
        <div><div class="text-sm text-muted">Name</div><div class="fw-700">{{ auth()->user()->name ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Email</div><div class="fw-700">{{ auth()->user()->email ?? '—' }}</div></div>
    </div>
</div>
@endsection
