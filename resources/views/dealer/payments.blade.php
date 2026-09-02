@extends('layouts.dealer')
@section('title', __('panel.payments.title').' - '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.payments.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.payments.sub') }}</p>
    </div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('dealer.payments') }}" class="panel-filter-form panel-filter-form--3">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.payments.search_placeholder') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.status') }}</label>
            <select name="status" class="form-input">
                <option value="">{{ __('panel.common.all_status') }}</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('panel.payments.paid') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('panel.payments.pending') }}</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('panel.payments.failed') }}</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.common.filter') }}</button>
            <a href="{{ route('dealer.payments') }}" class="btn btn--ghost">{{ __('panel.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="padding:0;">
    <div style="padding:1.25rem; border-bottom:1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
        <div class="fw-800" style="color:var(--gray-900)">{{ __('panel.payments.all_invoices_payments') }}</div>
        <div class="badge badge--primary">{{ __('panel.payments.records_count', ['count' => $invoices->count()]) }}</div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.payments.invoice_number') }}</th>
                <th>{{ __('panel.payments.date') }}</th>
                <th>{{ __('panel.payments.credits') }}</th>
                <th>{{ __('panel.payments.amount') }}</th>
                <th>{{ __('panel.payments.payment_id') }}</th>
                <th>{{ __('panel.payments.status') }}</th>
                <th>{{ __('panel.payments.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
            <tr>
                <td style="font-weight:700;">{{ $inv->invoice_number }}</td>
                <td>{{ $inv->created_at->format('d/m/Y') }}<br><span class="text-xs text-muted">{{ $inv->created_at->format('H:i:s') }}</span></td>
                <td>{{ number_format($inv->credits) }} {{ __('panel.payments.credits_suffix') }}</td>
                <td>£{{ number_format($inv->amount, 2) }}</td>
                <td><span class="text-xs">{{ $inv->payment_id ?: 'N/A' }}</span></td>
                <td>
                    @if($inv->status === 'paid' || $inv->status === 'success')
                        <span class="badge badge--success">{{ __('panel.payments.paid') }}</span>
                    @elseif($inv->status === 'pending')
                        <span class="badge badge--warning">{{ __('panel.payments.pending') }}</span>
                    @else
                        <span class="badge badge--danger">{{ ucfirst($inv->status) }}</span>
                    @endif
                </td>
                <td style="white-space:nowrap;">
                    <a href="{{ route('dealer.invoice', $inv->invoice_number) }}" class="text-teal fw-700">{{ __('panel.payments.view') }}</a>
                    <span class="text-muted" style="margin:0 6px;">|</span>
                    <a href="{{ route('dealer.invoice.download', $inv->invoice_number) }}" class="text-teal fw-700">{{ __('panel.payments.download') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 3rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📄</div>
                    <div class="text-muted">{{ __('panel.payments.no_records') }}</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($invoices->hasPages())
        <div style="padding:1rem">{{ $invoices->links('components.pagination') }}</div>
    @endif
</div>@endsection

