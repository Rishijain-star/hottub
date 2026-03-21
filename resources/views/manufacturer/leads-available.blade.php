@extends('layouts.manufacturer')
@section('title', 'Available Leads – Manufacturer Panel')
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">Available Leads</h1><p class="panel-page-sub">Leads created by admin, available for purchase</p></div></div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Postcode</th>
                <th>Interests</th>
                <th>Price</th>
                <th>Purchased</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            <tr>
                <td>
                    <div class="fw-700 text-dark">Name Hidden</div>
                    <div class="text-sm text-muted">Email Hidden</div>
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    @if(is_array($it->interests))
                        @foreach($it->interests as $tag)<span class="badge">{{ ucwords(str_replace('_',' ',$tag)) }}</span> @endforeach
                    @else — @endif
                </td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td>{{ $it->purchases->count() }} manufacturer(s) purchased</td>
                <td>
                    <form action="{{ route('manufacturer.leads.purchase', $it) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn--primary btn--sm">Buy</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1rem">No new leads available.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
@endsection
