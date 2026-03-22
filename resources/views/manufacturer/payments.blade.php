@extends('layouts.manufacturer')
@section('title', 'Accounting & Invoices – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Accounting & Invoices</h1>
        <p class="panel-page-sub">View all your credit purchases and payment history</p>
    </div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('manufacturer.payments') }}" class="grid grid--3" style="align-items: flex-end; gap: 1rem;">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Invoice #..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn--primary" style="flex: 1;">Filter</button>
            <a href="{{ route('manufacturer.payments') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

<div class="card" style="padding:0;">
    <div style="padding:1.25rem; border-bottom:1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
        <div class="fw-800" style="color:var(--gray-900)">All Invoices & Payments</div>
        <div class="badge badge--primary">{{ $invoices->count() }} Records</div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Credits</th>
                <th>Amount</th>
                <th>Payment ID</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
            <tr>
                <td style="font-weight:700;">{{ $inv->invoice_number }}</td>
                <td>{{ $inv->created_at->format('d/m/Y') }}<br><span class="text-xs text-muted">{{ $inv->created_at->format('H:i:s') }}</span></td>
                <td>{{ number_format($inv->credits) }} credits</td>
                <td>£{{ number_format($inv->amount, 2) }}</td>
                <td><span class="text-xs">{{ $inv->payment_id ?: 'N/A' }}</span></td>
                <td>
                    @if($inv->status === 'paid' || $inv->status === 'success')
                        <span class="badge badge--success">Paid</span>
                    @elseif($inv->status === 'pending')
                        <span class="badge badge--warning">Pending</span>
                    @else
                        <span class="badge badge--danger">{{ ucfirst($inv->status) }}</span>
                    @endif
                </td>
                <td><a href="{{ route('manufacturer.invoice.download', $inv->invoice_number) }}" class="text-teal fw-700">Download</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 3rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📄</div>
                    <div class="text-muted">No invoices or payment records found.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($invoices->hasPages())
        <div style="padding:1rem">{{ $invoices->links('components.pagination') }}</div>
    @endif
</div>@endsection

