@extends('layouts.dealer')
@section('title', 'My Inventory – Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">My Inventory</h1>
        <p class="panel-page-sub">Manage hot tubs and swim spas in your stock</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <div class="panel-stat-card" style="padding: 0.5rem 1rem; flex-direction: row; gap: 1rem; margin-bottom: 0;">
            <div class="text-sm text-muted">Total Items:</div>
            <div class="fw-800 text-primary">{{ $inventoryCount }}</div>
        </div>
        <button class="btn btn--primary btn--pill">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
            Add Item
        </button>
    </div>
</div>

<div class="card" style="padding: 3rem; text-align: center; background: #f8fafc; border: 2px dashed #e2e8f0;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🛁</div>
    <h2 class="fw-800" style="color: var(--gray-900); margin-bottom: 0.5rem;">Inventory Manager</h2>
    <p class="text-muted" style="max-width: 500px; margin: 0 auto 2rem;">
        List the hot tubs and swim spas you have available in stock. This section is currently being connected to the public search engine.
    </p>
    <div class="badge badge--primary" style="padding: 0.5rem 1rem;">Coming Soon: Live Sync with Public Site</div>
</div>
@endsection