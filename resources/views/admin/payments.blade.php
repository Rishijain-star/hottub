@extends('layouts.admin')
@section('title', __('panel.admin.pages.payments_index.title') . ' – Admin Panel')
@section('styles')
<style>
    .invoice-number-text {
        font-weight: 700;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .payment-id-cell {
        max-width: 170px;
    }
    .payment-id-text {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: bottom;
    }
</style>
@endsection
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.payments_index.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.payments_index.sub') }}</p>
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
    <div class="mt-4" style="padding:1rem">{{ $creditRequests->appends(request()->except('credits_page'))->links('components.pagination') }}</div>
</div>

<div class="card" style="padding: 0; margin-top: 2rem;">
    <div style="padding: 1.25rem; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
        <div class="fw-800" style="color:var(--gray-900)">Gateway Payments & Invoices</div>
        <div class="text-sm text-muted">Showing all Stripe/PayPal transactions</div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>User / Company</th>
                <th>Credits</th>
                <th>Amount (£)</th>
                <th>Gateway Status</th>
                <th>Payment ID</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
            <tr>
                <td>
                    <span class="invoice-number-text">{{ $inv->invoice_number }}</span>
                </td>
                <td>
                    <div class="fw-700">{{ $inv->user->name ?? 'N/A' }}</div>
                    <div class="text-xs text-muted">{{ $inv->user->company_name ?? $inv->user->email ?? 'N/A' }}</div>
                </td>
                <td>{{ number_format($inv->credits) }}</td>
                <td>£{{ number_format($inv->amount, 2) }}</td>
                <td>
                    @if($inv->status === 'paid' || $inv->status === 'success')
                        <span class="badge badge--success">Paid (Success)</span>
                    @elseif($inv->status === 'failed')
                        <span class="badge badge--danger">Failed</span>
                    @elseif($inv->status === 'pending')
                        <span class="badge badge--warning">Pending</span>
                    @else
                        <span class="badge">{{ ucfirst($inv->status) }}</span>
                    @endif
                </td>
                <td class="payment-id-cell">
                    @php($paymentId = (string) ($inv->payment_id ?: 'N/A'))
                    <span class="text-xs payment-id-text" title="{{ $paymentId }}">{{ $paymentId }}</span>
                </td>
                <td class="text-sm">{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                <td style="white-space:nowrap;">
                    <a href="{{ route('admin.invoice', $inv->invoice_number) }}" class="text-teal fw-700">View</a>
                    <span class="text-muted" style="margin:0 6px;">|</span>
                    <a href="{{ route('admin.invoice.download', $inv->invoice_number) }}" class="text-teal fw-700">Download</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted" style="padding: 3rem;">No gateway payments found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $invoices->appends(request()->except('invoices_page'))->links('components.pagination') }}</div>
</div>
@endsection

@section('scripts')
@endsection
