@extends('layouts.admin')
@section('title', 'Dashboard Overview – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Dashboard Overview</h1>
        <p class="panel-page-sub">Platform performance at a glance</p>
    </div>
</div>

<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#eef7ff;"><svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="panel-stat-card__label">Total Leads Generated</div>
        <div class="panel-stat-card__value">{{ $leadsTotal }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#f5f3ff;"><svg width="22" height="22" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg></div>
        <div class="panel-stat-card__label">Leads Purchased (Dealers)</div>
        <div class="panel-stat-card__value">{{ $dealerPurchasedCount }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#f5f3ff;"><svg width="22" height="22" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg></div>
        <div class="panel-stat-card__label">Leads Purchased (Manufacturers)</div>
        <div class="panel-stat-card__value">{{ $manufacturerPurchasedCount }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#ecfdf5;"><svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="panel-stat-card__label">Total Converted Sales</div>
        <div class="panel-stat-card__value">{{ $totalConverted }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fff7ed;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 2 2 5-5"/></svg></div>
        <div class="panel-stat-card__label">Overall Conversion Rate</div>
        <div class="panel-stat-card__value">{{ number_format($overallConversionRate, 1) }}%</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fff7ed;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 2 2 5-5"/></svg></div>
        <div class="panel-stat-card__label">Dealer Conversion Rate</div>
        <div class="panel-stat-card__value">{{ number_format($dealerConversionRate, 1) }}%</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fff7ed;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 2 2 5-5"/></svg></div>
        <div class="panel-stat-card__label">Manufacturer Conversion Rate</div>
        <div class="panel-stat-card__value">{{ number_format($manufacturerConversionRate, 1) }}%</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#ecfdf5;"><svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9a4 4 0 0 0 0 8h6a4 4 0 0 1 0 8H6"/></svg></div>
        <div class="panel-stat-card__label">Revenue from Leads</div>
        <div class="panel-stat-card__value">£{{ number_format($revenue, 2) }}</div>
    </div>
</div>

@if($dealersPending > 0)
<div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;background:#fff7ed;border:1px solid #fde68a;margin-bottom:1rem">
    <div>
        <div class="fw-800" style="color:#92400e">{{ $dealersPending }} Dealers Awaiting Approval</div>
        <div class="text-sm text-muted">Review and approve new dealer applications</div>
    </div>
    <a href="{{ route('admin.dealers.index') }}" class="btn btn--primary">Review Now</a>
</div>
@endif

@php
    $supportRequests = \App\Models\Message::where('receiver_id', 1)
        ->whereHas('sender', function($q) { $q->whereIn('role', ['dealer', 'manufacturer']); })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
@endphp

@if($supportRequests->count() > 0)
<div class="card" style="margin-bottom:1rem">
    <div class="fw-800 mb-3" style="font-size:1.05rem;color:var(--gray-900)">Recent Support Requests</div>
    <div style="display:flex;flex-direction:column;gap:0.75rem">
        @foreach($supportRequests as $req)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:var(--gray-50);border-radius:8px;border:1px solid var(--gray-200)">
                <div>
                    <div style="font-weight:700;font-size:0.9rem">{{ $req->sender->name }} ({{ ucfirst($req->sender->role) }})</div>
                    <div style="font-size:0.85rem;color:var(--gray-600);margin-top:2px">{{ $req->content }}</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:0.75rem;color:var(--gray-400)">{{ $req->created_at->diffForHumans() }}</div>
                    <span class="badge {{ $req->sender->status === 'approved' ? 'badge--success' : 'badge--dark' }}" style="margin-top:4px">{{ ucfirst($req->sender->status) }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:1rem">
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add Hot Tub</div>
        <div class="text-sm text-muted">Create new product listing with images and specs</div>
    </div>
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Approve Dealers</div>
        <div class="text-sm text-muted">Review pending dealer applications</div>
    </div>
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">View Leads</div>
        <div class="text-sm text-muted">Monitor lead activity and conversions</div>
    </div>
</div>
@endsection
