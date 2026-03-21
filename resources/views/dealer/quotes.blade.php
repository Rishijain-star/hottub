@extends('layouts.dealer')
@section('title', 'Available Leads – Dealer Panel')
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
                <td><span class="text-sm">{{ $cnt }} dealer{{ $cnt===1?'':'s' }} purchased</span></td>
                <td>
                    @if($iBought)
                        <a class="btn btn--ghost btn--sm" href="{{ route('dealer.leads.view', $it) }}">View</a>
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

@section('scripts')
<script>
document.addEventListener('click', async function(e){
    const btn = e.target.closest('.js-buy-lead');
    if (!btn) return;
    btn.disabled = true;
    const id = btn.getAttribute('data-id');
    try{
        const res = await fetch('{{ route('dealer.leads.buy', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        });
        const data = await res.json();
        const row = document.querySelector('tr[data-lead-id="'+id+'"]');
        if (res.ok && data.ok){
            // Update table row with real details
            const nameDiv = row?.querySelector('.fw-700.text-dark');
            const emailDiv = row?.querySelector('.text-sm.text-muted');
            if (nameDiv) nameDiv.textContent = data.lead.name;
            if (emailDiv) emailDiv.textContent = data.lead.email;

            const purchasedCell = row?.querySelector('td:nth-child(5)');
            if (purchasedCell) purchasedCell.innerHTML = '<span class="text-sm">'+data.count+' dealer'+(data.count===1?'':'s')+' purchased</span>';
            const actionsCell = row?.querySelector('td:nth-child(6)');
            if (actionsCell) {
                if (data.limitReached) {
                    actionsCell.innerHTML = '<button class=\"btn btn--ghost btn--sm\" disabled>Sold Out</button>';
                } else {
                    actionsCell.innerHTML = '<a class=\"btn btn--ghost btn--sm\" href=\"'+('{{ route('dealer.leads.view', ':id') }}'.replace(':id', id))+'\">View</a>';
                }
            }
        } else {
            alert(data.msg || 'Unable to purchase lead.');
            btn.disabled = false;
        }
    }catch(err){
        alert('Network error.');
        btn.disabled = false;
    }
});
</script>
@endsection
@endsection
