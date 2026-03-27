@extends('layouts.dealer')
@section('title', 'Available Leads – Dealer Panel')
@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">Available Leads (<span id="availableLeadsCountHeader">{{ $items->total() }}</span>)</h1>
        <p class="panel-page-sub">Leads created by admin, available for purchase</p>
    </div>
    <button type="button" class="btn btn--sm" id="jsRefreshAvailableLeads" style="display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:36px;padding:0 14px;border:1px solid #0ea5a3;background:#ffffff;color:#0f172a;font-weight:700;border-radius:8px;line-height:1;">Refresh</button>
</div>
<div id="availableLeadsListMount">
    @include('dealer.partials.quotes-available-list')
</div>
@endsection

@section('scripts')
<script>
document.getElementById('jsRefreshAvailableLeads')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const page = new URLSearchParams(window.location.search).get('page') || '1';
    try {
        const res = await fetch('{{ route('dealer.quotes') }}?fragment=1&page=' + encodeURIComponent(page), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        const mount = document.getElementById('availableLeadsListMount');
        if (mount && data.html) mount.innerHTML = data.html;
        const h = document.getElementById('availableLeadsCountHeader');
        if (h && typeof data.total !== 'undefined') h.textContent = data.total;
    } catch (e) {
        alert('Unable to refresh list.');
    } finally {
        btn.disabled = false;
    }
});
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
            if (data.msg && data.msg.includes('already been closed by another dealer')) {
                const row = document.querySelector('tr[data-lead-id="'+id+'"]');
                if (row) {
                    row.remove();
                    // Update header count
                    const countSpan = document.getElementById('availableLeadsCountHeader');
                    if (countSpan) {
                        let count = parseInt(countSpan.textContent) || 0;
                        if (count > 0) countSpan.textContent = count - 1;
                    }
                }
            }
            btn.disabled = false;
        }
    }catch(err){
        alert('Network error.');
        btn.disabled = false;
    }
});
</script>
@endsection
