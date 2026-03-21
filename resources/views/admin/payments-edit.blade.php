@extends('layouts.admin')
@section('title', 'Edit Invoice – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Edit Invoice</h1><p class="panel-page-sub">{{ $item->invoice_number }}</p></div>
    <a href="{{ route('admin.payments') }}" class="btn">Back</a>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.payments.update', $item) }}">
        @csrf @method('PUT')
        <div class="grid grid--3">
            <div class="form-group"><label class="form-label">Invoice #</label><input class="form-input" name="invoice_number" value="{{ old('invoice_number',$item->invoice_number) }}"></div>
            <div class="form-group"><label class="form-label">Dealer ID</label><input class="form-input" name="dealer_id" value="{{ old('dealer_id',$item->dealer_id) }}"></div>
            <div class="form-group"><label class="form-label">Credits</label><input class="form-input" type="number" min="0" name="credits" value="{{ old('credits',$item->credits) }}" required></div>
            <div class="form-group"><label class="form-label">Amount</label><input class="form-input" type="number" step="0.01" min="0" name="amount" value="{{ old('amount',$item->amount) }}" required></div>
            <div class="form-group"><label class="form-label">Status</label><select class="form-input" name="status"><option value="paid" @selected(old('status',$item->status)=='paid')>PAID</option><option value="pending" @selected(old('status',$item->status)=='pending')>PENDING</option><option value="failed" @selected(old('status',$item->status)=='failed')>FAILED</option></select></div>
            <div class="form-group"><label class="form-label">Payment ID</label><input class="form-input" name="payment_id" value="{{ old('payment_id',$item->payment_id) }}"></div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start"><label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_number"> Regenerate number</label><button class="btn btn--primary">Save Changes</button></div>
    </form>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Invoice #</th><th>Dealer</th><th>Credits</th><th>Amount</th><th>Status</th><th>Date</th><th>Payment ID</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->invoice_number }}</td>
                <td>{{ $it->dealer_id ?? 'N/A' }}</td>
                <td>{{ $it->credits }}</td>
                <td>£{{ number_format($it->amount,2) }}</td>
                <td>@if($it->status==='paid')<span class="badge badge--success">PAID</span>@elseif($it->status==='failed')<span class="badge badge--dark">FAILED</span>@else<span class="badge">PENDING</span>@endif</td>
                <td>{{ $it->created_at }}</td>
                <td>{{ $it->payment_id ?? 'N/A' }}</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.payments.edit', $it) }}" class="icon-btn" title="Edit">✎</a>
                        <form method="POST" action="{{ route('admin.payments.destroy', $it) }}" onsubmit="return confirm('Delete this invoice?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn" title="Delete">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@endsection
