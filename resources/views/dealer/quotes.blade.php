@extends('layouts.dealer')
@section('title', __('panel.nav.available_leads').' – '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">{{ __('panel.quotes.title', ['count' => $items->total()]) }}</h1>
        <p class="panel-page-sub">{{ __('panel.quotes.sub') }}</p>
    </div>
    <button type="button" class="btn btn--sm" id="jsRefreshAvailableLeads" style="display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:36px;padding:0 14px;border:1px solid #0ea5a3;background:#ffffff;color:#0f172a;font-weight:700;border-radius:8px;line-height:1;">{{ __('panel.common.refresh') }}</button>
</div>
<div id="availableLeadsListMount">
    @include('dealer.partials.quotes-available-list')
</div>
@endsection

@php
    $quotesPanelI18n = [
        'view' => __('panel.common.view'),
        'soldOut' => __('panel.common.sold_out'),
        'dealersPurchasedOne' => trans_choice('panel.common.dealers_purchased', 1, ['count' => 1]),
        'dealersPurchasedOther' => trans_choice('panel.common.dealers_purchased', 2, ['count' => '__COUNT__']),
        'unableRefresh' => __('panel.common.unable_refresh'),
        'networkError' => __('panel.common.network_error'),
    ];
@endphp

@section('scripts')
<script>
const panelI18n = @json($quotesPanelI18n);
function formatDealersPurchased(count) {
    return count === 1 ? panelI18n.dealersPurchasedOne : panelI18n.dealersPurchasedOther.replace('__COUNT__', count);
}
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
    } catch (e) {
        alert(panelI18n.unableRefresh);
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
            const nameDiv = row?.querySelector('.fw-700.text-dark');
            const emailDiv = row?.querySelector('.text-sm.text-muted');
            if (nameDiv) nameDiv.textContent = data.lead.name;
            if (emailDiv) emailDiv.textContent = data.lead.email;

            const purchasedCell = row?.querySelector('td:nth-child(5)');
            if (purchasedCell) {
                purchasedCell.innerHTML = '<span class="text-sm">'+formatDealersPurchased(data.count)+'</span>';
            }
            const actionsCell = row?.querySelector('td:nth-child(6)');
            if (actionsCell) {
                if (data.limitReached) {
                    actionsCell.innerHTML = '<button class="btn btn--ghost btn--sm" disabled>'+panelI18n.soldOut+'</button>';
                } else {
                    actionsCell.innerHTML = '<a class="btn btn--ghost btn--sm" href="'+('{{ route('dealer.leads.view', ':id') }}'.replace(':id', id))+'">'+panelI18n.view+'</a>';
                }
            }
        } else {
            alert(data.msg || panelI18n.networkError);
            if (data.msg && data.msg.includes('already been closed by another dealer')) {
                if (row) row.remove();
            }
            btn.disabled = false;
        }
    }catch(err){
        alert(panelI18n.networkError);
        btn.disabled = false;
    }
});
</script>
@endsection
