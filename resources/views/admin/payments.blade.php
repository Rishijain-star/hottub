@extends('layouts.admin')
@section('title', 'Payments & Credit Requests – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Payments & Credit Requests</h1>
        <p class="panel-page-sub">Review dealer credit requests and approve or reject them</p>
    </div>
</div>

@if(session('success')) <div class="alert alert--success" style="margin-bottom: 1.5rem; padding: 1rem; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert--danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #fef2f2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca;">{{ session('error') }}</div> @endif

<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__label">Total Revenue (Paid)</div>
        <div class="panel-stat-card__value">£{{ number_format($revenue, 2) }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__label">Pending Requests</div>
        <div class="panel-stat-card__value">{{ $pending }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__label">Approved Requests</div>
        <div class="panel-stat-card__value">{{ $completed }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__label">Rejected Requests</div>
        <div class="panel-stat-card__value">{{ $failed }}</div>
    </div>
</div>

<div class="card" style="padding: 0; margin-top: 2rem;">
    <div style="padding: 1.25rem; border-bottom: 1px solid var(--gray-200);">
        <div class="fw-800" style="color:var(--gray-900)">Incoming Credit Requests</div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>User / Company</th>
                <th>Role</th>
                <th>Credits Requested</th>
                <th>Amount (£)</th>
                <th>Status</th>
                <th>Requested On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($creditRequests as $req)
            <tr>
                <td>
                    <div class="fw-700">{{ $req->user->name }}</div>
                    <div class="text-xs text-muted">{{ $req->user->company_name ?: $req->user->email }}</div>
                </td>
                <td><span class="badge">{{ ucfirst($req->user->role) }}</span></td>
                <td class="fw-700">{{ number_format($req->credits) }}</td>
                <td>£{{ number_format($req->amount ?: 0, 2) }}</td>
                <td>
                    @if($req->status === 'approved')
                        <span class="badge badge--success">Approved</span>
                    @elseif($req->status === 'rejected')
                        <span class="badge badge--danger">Rejected</span>
                    @else
                        <span class="badge badge--warning">Pending</span>
                    @endif
                </td>
                <td class="text-sm">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($req->status === 'pending')
                        <div style="display: flex; gap: 0.5rem;">
                            <form action="{{ route('admin.credits.approve', $req) }}" method="POST" onsubmit="return confirm('Approve this credit request?')">
                                @csrf
                                <button type="submit" class="btn btn--sm btn--primary">Approve</button>
                            </form>
                            <form action="{{ route('admin.credits.reject', $req) }}" method="POST" onsubmit="return confirm('Reject this credit request?')">
                                @csrf
                                <button type="submit" class="btn btn--sm btn--ghost" style="color: var(--danger);">Reject</button>
                            </form>
                        </div>
                    @else
                        <span class="text-xs text-muted">Processed</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding: 3rem;">No credit requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
