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
            @php $cnt = (int) ($counts[$it->id] ?? 0); $iBought = in_array($it->id, $mine ?? []); @endphp
            <tr data-lead-id="{{ $it->id }}">
                <td>
                    <div class="fw-700 text-dark">{{ $iBought ? $it->name : 'Name Hidden' }}</div>
                    <div class="text-sm text-muted">{{ $iBought ? $it->email : 'Email Hidden' }}</div>
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
                <td><span class="text-sm">{{ $cnt }} manufacturer{{ $cnt===1?'':'s' }} purchased</span></td>
                <td>
                    @if($iBought)
                        <a class="btn btn--ghost btn--sm" href="{{ route('manufacturer.leads.view', $it) }}">View</a>
                    @elseif($cnt >= 3)
                        <button class="btn btn--ghost btn--sm" disabled>Sold Out</button>
                    @else
                        <button class="btn btn--primary btn--sm js-buy-lead" data-id="{{ $it->id }}">Buy</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1rem">No leads available.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
<div class="modal-backdrop" id="leadDetailModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Lead Details</div>
            <button type="button" class="modal-close" data-close="#leadDetailModal">✕</button>
        </div>
        <div class="form-group" style="margin-bottom:12px">
            <div class="text-sm text-muted" style="margin-bottom:6px">Stage</div>
            <div id="ldStageBar" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                @php $stages = ['New Lead','Contacted','Nurturing','Sale Pending','Site Visit','Delivered']; @endphp
                @foreach($stages as $s)
                <button class="badge js-stage" data-stage="{{ $s }}" style="cursor:pointer">{{ $s }}</button>
                @endforeach
            </div>
        </div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Name</label><div id="ldName" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">Email</label><div id="ldEmail" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">Phone</label><div id="ldPhone" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">Postcode</label><div id="ldPostcode" class="text-sm"></div></div>
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">Message</label><div id="ldMessage" class="text-sm"></div></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" data-close="#leadDetailModal">Close</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('click', async function(e){
    const btn = e.target.closest('.js-buy-lead');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    try{
        const res = await fetch('{{ route('manufacturer.leads.buy', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}' }
        });
        const data = await res.json();
        if (res.ok && data.ok){
            // Update table row with real details
            const row = btn.closest('tr');
            if (row) {
                const nameDiv = row.querySelector('.fw-700.text-dark');
                const emailDiv = row.querySelector('.text-sm.text-muted');
                if (nameDiv) nameDiv.textContent = data.lead.name;
                if (emailDiv) emailDiv.textContent = data.lead.email;
            }
            const viewUrl = '{{ route('manufacturer.leads.view', ':id') }}'.replace(':id', id);
            btn.outerHTML = '<a class="btn btn--ghost btn--sm" href="'+viewUrl+'">View</a>';
        } else {
            alert(data.msg || 'Unable to purchase lead');
        }
    }catch(err){ alert('Network error'); }
});
</script>
@endsection
@endsection
