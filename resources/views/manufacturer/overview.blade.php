@extends('layouts.manufacturer')
@section('title', 'Overview – Manufacturer Panel')
@section('styles')
<style>.steps{display:flex;flex-direction:column;gap:.6rem}.step{display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;border:1px solid #e3edff;background:#f5f9ff;border-radius:var(--r-lg);font-weight:600;color:#1e3a8a}.step__num{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#2563eb;color:#fff;font-size:.8rem}.step__text{flex:1}</style>
@endsection
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Dashboard</h1><p class="panel-page-sub">Welcome back. Track your credits, leads and performance</p></div>
    </div>
@php($me = auth()->user())
<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#ecfdf5;"><svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9a4 4 0 0 0 0 8h6a4 4 0 0 1 0 8H6"/></svg></div>
        <div class="panel-stat-card__label">Available Credits</div>
        <div class="panel-stat-card__value">{{ $availableCredits }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#eff6ff;"><svg width="22" height="22" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="panel-stat-card__label">Available Leads</div>
        <div class="panel-stat-card__value">{{ $availableLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#faf5ff;"><svg width="22" height="22" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><path d="M8 17l4-4 4 4M12 12V3"/></svg></div>
        <div class="panel-stat-card__label">Purchased Leads</div>
        <div class="panel-stat-card__value">{{ $purchasedLeadsCount }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fff7ed;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <div class="panel-stat-card__label">Active Leads</div>
        <div class="panel-stat-card__value">{{ $activeLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#dcfce7;"><svg width="22" height="22" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="panel-stat-card__label">Converted Leads</div>
        <div class="panel-stat-card__value">{{ $convertedLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fee2e2;"><svg width="22" height="22" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="panel-stat-card__label">Lost Leads</div>
        <div class="panel-stat-card__value">{{ $lostLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fef9c3;"><svg width="22" height="22" fill="none" stroke="#ca8a04" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 2 2 5-5"/></svg></div>
        <div class="panel-stat-card__label">Conversion %</div>
        <div class="panel-stat-card__value">{{ $conversionRate }}%</div>
    </div>
</div>
<div class="card" style="margin-top:1rem">
    <div class="fw-800" style="margin-bottom:.5rem">Quick Actions</div>
    <div class="steps">
        <a class="step" href="{{ route('manufacturer.profile') }}"><div class="step__num">1</div><div class="step__text">View My Profile</div></a>
        <a class="step" href="{{ route('manufacturer.quotes') }}"><div class="step__num">2</div><div class="step__text">Browse Available Leads</div></a>
        <a class="step" href="{{ route('manufacturer.leads') }}"><div class="step__num">3</div><div class="step__text">Manage My Leads</div></a>
    </div>
</div>
@endsection

