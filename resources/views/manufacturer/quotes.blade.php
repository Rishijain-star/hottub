@extends('layouts.manufacturer')
@section('title', 'Available Leads – Manufacturer Panel')
@section('content')
<div class="panel-page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 class="panel-page-title">Available Leads (<span id="availableLeadsCountHeader">{{ $items->total() }}</span>)</h1>
        <p class="panel-page-sub">Leads created by admin, available for purchase</p>
    </div>
    <button type="button" class="btn btn--sm" id="jsRefreshAvailableLeads" style="display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:36px;padding:0 14px;border:1px solid #0ea5a3;background:#ffffff;color:#0f172a;font-weight:700;border-radius:8px;line-height:1;">Refresh</button>
</div>
<div id="availableLeadsListMount">
    @include('manufacturer.partials.quotes-available-list')
</div>
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
        alert('Unable to refresh list.');
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
            if (data.msg && data.msg.includes('already been closed by another dealer')) {
                const row = btn.closest('tr');
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
        }
    }catch(err){ alert('Network error'); }
});
</script>
@endsection
