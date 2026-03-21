@extends('layouts.customer')
@section('title', 'My Hot Tub – Customer Panel')
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">My Hot Tub</h1><p class="panel-page-sub">Your ownership details</p></div></div>
<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Unit Information</div>
    <div class="grid grid--2">
        <div><div class="text-sm text-muted">Brand</div><div class="fw-700">{{ $lead?->delivery_details['make'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Model</div><div class="fw-700">{{ $lead?->delivery_details['model'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Shell Colour</div><div class="fw-700">{{ $lead?->delivery_details['shell_colour'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Cabinet Colour</div><div class="fw-700">{{ $lead?->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Accessories</div><div class="fw-700">{{ $lead?->delivery_details['accessories'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Sale Price</div><div class="fw-700">{{ ($lead && isset($lead->delivery_details['sale_price'])) ? '£' . number_format($lead->delivery_details['sale_price'], 2) : '—' }}</div></div>
        <div><div class="text-sm text-muted">Purchased On</div><div class="fw-700">{{ ($lead && isset($lead->delivery_details['delivery_date'])) ? \Carbon\Carbon::parse($lead->delivery_details['delivery_date'])->format('M d, Y') : '—' }}</div></div>
        <div>
            <div class="text-sm text-muted">Documents</div>
            <div class="fw-700" style="display:flex;gap:10px;margin-top:5px">
                @if($lead && $lead->invoice_path)
                    <a href="{{ asset('storage/' . $lead->invoice_path) }}" target="_blank" class="btn btn--ghost btn--sm">View Invoice</a>
                @endif
                @if($lead && $lead->warranty_path)
                    <a href="{{ asset('storage/' . $lead->warranty_path) }}" target="_blank" class="btn btn--ghost btn--sm">View Warranty</a>
                @endif
                @if(!$lead || (!$lead->invoice_path && !$lead->warranty_path))
                    —
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
