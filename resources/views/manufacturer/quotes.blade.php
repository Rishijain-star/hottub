@extends('layouts.manufacturer')
@section('title', __('panel.nav.available_leads').' – '.__('panel.manufacturer_title'))
@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">{{ __('panel.quotes.title', ['count' => $items->total()]) }}</h1>
        <p class="panel-page-sub">{{ __('panel.quotes.sub') }}</p>
    </div>
    <button type="button" class="btn btn--sm" id="jsRefreshAvailableLeads" style="display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:36px;padding:0 14px;border:1px solid #0ea5a3;background:#ffffff;color:#0f172a;font-weight:700;border-radius:8px;line-height:1;">{{ __('panel.common.refresh') }}</button>
</div>
<div id="availableLeadsListMount">
    @include('manufacturer.partials.quotes-available-list')
</div>
<div class="modal-backdrop" id="leadDetailModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">{{ __('panel.common.lead_details') }}</div>
            <button type="button" class="modal-close" data-close="#leadDetailModal">✕</button>
        </div>
        <div class="form-group" style="margin-bottom:12px">
            <div class="text-sm text-muted" style="margin-bottom:6px">{{ __('panel.common.stage') }}</div>
            <div id="ldStageBar" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                @foreach(['new_lead','contacted','nurturing','sale_pending','site_visit','delivered'] as $stageKey)
                <button class="badge js-stage" data-stage="{{ __('panel.stages.'.$stageKey) }}" style="cursor:pointer">{{ __('panel.stages.'.$stageKey) }}</button>
                @endforeach
            </div>
        </div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.common.name') }}</label><div id="ldName" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">{{ __('panel.common.email') }}</label><div id="ldEmail" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">{{ __('panel.common.phone') }}</label><div id="ldPhone" class="text-sm"></div></div>
            <div class="form-group"><label class="form-label">{{ __('panel.common.postcode') }}</label><div id="ldPostcode" class="text-sm"></div></div>
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">{{ __('panel.common.message') }}</label><div id="ldMessage" class="text-sm"></div></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" data-close="#leadDetailModal">{{ __('panel.common.close') }}</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('jsRefreshAvailableLeads')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const page = new URLSearchParams(window.location.search).get('page') || '1';
    try {
        const res = await fetch('{{ route('manufacturer.quotes') }}?fragment=1&page=' + encodeURIComponent(page), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        const mount = document.getElementById('availableLeadsListMount');
        if (mount && data.html) mount.innerHTML = data.html;
        const h = document.getElementById('availableLeadsCountHeader');
        if (h && typeof data.total !== 'undefined') h.textContent = data.total;
    } catch (e) {
        alert(@json(__('panel.common.unable_refresh')));
    } finally {
        btn.disabled = false;
    }
});
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
            const row = btn.closest('tr');
            if (row) {
                const nameDiv = row.querySelector('.fw-700.text-dark');
                const emailDiv = row.querySelector('.text-sm.text-muted');
                if (nameDiv) nameDiv.textContent = data.lead.name;
                if (emailDiv) emailDiv.textContent = data.lead.email;
            }
            const viewUrl = '{{ route('manufacturer.leads.view', ':id') }}'.replace(':id', id);
            btn.outerHTML = '<a class="btn btn--ghost btn--sm" href="'+viewUrl+'">{{ __('panel.common.view') }}</a>';
        } else {
            alert(data.msg || @json(__('panel.common.network_error')));
        }
    }catch(err){ alert(@json(__('panel.common.network_error'))); }
});
</script>
@endsection
