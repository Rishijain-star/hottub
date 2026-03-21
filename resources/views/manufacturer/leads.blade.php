@extends('layouts.manufacturer')
@section('title', 'My Leads – Manufacturer Panel')
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">My Leads</h1><p class="panel-page-sub">Leads you have purchased</p></div></div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Postcode</th>
                <th>Interests</th>
                <th>Price</th>
                <th>Purchased On</th>
                <th>Stage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            @php $purchase = $purchases[$it->id] ?? null; @endphp
            <tr>
                <td>
                    @if($purchase && $purchase->stage === 'Lost')
                        <div class="fw-700 text-dark">Name Hidden</div>
                        <div class="text-sm text-muted">Email Hidden</div>
                    @else
                        <div class="fw-700 text-dark">{{ $it->name }}</div>
                        <div class="text-sm text-muted">{{ $it->email }}</div>
                    @endif
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    @if(is_array($it->interests) && count($it->interests))
                        @foreach($it->interests as $tag)
                            <span class="badge">{{ ucwords(str_replace('_',' ',$tag)) }}</span>
                        @endforeach
                    @else
                        —
                    @endif
                </td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td class="text-sm">{{ $purchase ? $purchase->created_at->format('d M Y') : '—' }}</td>
                <td>
                    <div class="text-sm fw-700 text-dark">{{ $purchase ? $purchase->stage : 'New Lead' }}</div>
                </td>
                <td>
                    <a href="{{ route('manufacturer.leads.view', $it->id) }}" class="btn btn--ghost btn--sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted" style="text-align:center;padding:1rem">No leads purchased yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
@endsection

