@extends('layouts.admin')
@section('title', 'Dashboard Overview – Admin Panel')
@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">Dashboard Overview</h1>
        <p class="panel-page-sub">Platform performance at a glance</p>
    </div>
    <form method="GET" action="{{ route('admin.overview') }}" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <select name="month" class="form-input" style="min-width:180px;">
            @foreach($monthOptions as $value => $label)
                <option value="{{ $value }}" {{ $selectedMonth === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--ghost btn--sm">Apply</button>
        <a href="{{ route('admin.overview.report', ['month' => $selectedMonth]) }}" class="btn btn--primary btn--sm">Download Analytics Report</a>
    </form>
</div>

@if(($pendingPartnerRegistrations ?? 0) > 0)
<div class="card" style="margin-bottom:1.5rem;padding:1.25rem 1.5rem;border:1px solid #fde68a;background:#fffbeb;border-radius:12px;">
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
        <div>
            <div class="fw-800" style="font-size:1.05rem;color:#92400e;">New partner sign-ups awaiting review</div>
            <p class="text-sm text-muted" style="margin:0.35rem 0 0;">{{ $pendingPartnerRegistrations }} pending ({{ $dealersPending ?? 0 }} dealers, {{ $manufacturersPending ?? 0 }} manufacturers)</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
            <a href="{{ route('admin.dealers.index') }}" class="btn btn--primary btn--sm">Review dealers</a>
            <a href="{{ route('admin.manufacturers') }}" class="btn btn--outline btn--sm">Review manufacturers</a>
        </div>
    </div>
</div>
@endif

<style>
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .stat-card-modern {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .stat-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .stat-card-modern__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items:center;
        justify-content:center;
        margin-bottom: 1rem;
    }
    .stat-card-modern__label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .stat-card-modern__value {
        color: #1e293b;
        font-size: 1.5rem;
        font-weight: 800;
    }
    @media (max-width: 1400px) {
        .admin-stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .admin-stats-grid { grid-template-columns: repeat(1, 1fr); }
    }
</style>

<div class="admin-stats-grid">
    {{-- Row 1 --}}
    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#eff6ff; color:#3b82f6;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-card-modern__label">Total Leads</div>
        <div class="stat-card-modern__value">{{ $leadsTotal }}</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#fff7ed; color:#f97316;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-card-modern__label">Active Leads</div>
        <div class="stat-card-modern__value">{{ $activeLeadsCount }}</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#f5f3ff; color:#8b5cf6;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        </div>
        <div class="stat-card-modern__label">Dealer Leads</div>
        <div class="stat-card-modern__value">{{ $dealerPurchasedCount }}</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#f0f9ff; color:#0ea5e9;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        </div>
        <div class="stat-card-modern__label">Manuf. Leads</div>
        <div class="stat-card-modern__value">{{ $manufacturerPurchasedCount }}</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#ecfdf5; color:#10b981;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-card-modern__label">Total Won</div>
        <div class="stat-card-modern__value">{{ $totalConverted }}</div>
    </div>

    {{-- Row 2 --}}
    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#fff1f2; color:#f43f5e;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1.5 17.5"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="stat-card-modern__label">Conversion Rate</div>
        <div class="stat-card-modern__value">{{ number_format($overallConversionRate, 1) }}%</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#f8fafc; color:#475569;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="stat-card-modern__label">Dealer Rate</div>
        <div class="stat-card-modern__value">{{ number_format($dealerConversionRate, 1) }}%</div>
    </div>

    <div class="stat-card-modern">
        <div class="stat-card-modern__icon" style="background:#fdf4ff; color:#d946ef;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-card-modern__label">Manuf. Rate</div>
        <div class="stat-card-modern__value">{{ number_format($manufacturerConversionRate, 1) }}%</div>
    </div>

    <div class="stat-card-modern" style="grid-column: span 2; background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="stat-card-modern__label" style="color:rgba(255,255,255,0.8);">Total Revenue</div>
                <div class="stat-card-modern__value" style="color:#fff; font-size:2rem;">£{{ number_format($revenue, 2) }}</div>
            </div>
            <div style="width:56px; height:56px; background:rgba(255,255,255,0.2); border-radius:14px; display:flex; align-items:center; justify-content:center; color:#fff;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
    </div>
</div>

@if($dealersPending > 0 && auth()->user()?->isFullAdmin())
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
