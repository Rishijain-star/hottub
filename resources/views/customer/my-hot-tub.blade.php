@extends('layouts.customer')
@section('title', __('panel.nav.my_hot_tub').' – '.__('panel.customer_title'))
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">{{ __('panel.nav.my_hot_tub') }}</h1><p class="panel-page-sub">{{ __('panel.customer_panel.my_hot_tub_sub') }}</p></div></div>
@forelse($leads as $lead)
<div class="card mb-4">
    <div class="fw-800 mb-3" style="font-size:1.1rem;color:var(--gray-900); display: flex; justify-content: space-between; align-items: center;">
        <span>{{ __('panel.customer_panel.unit_information') }} - {{ $lead->delivery_details['make'] ?? 'Product' }} {{ $lead->delivery_details['model'] ?? '' }}</span>
        @if($lead->stage === 'Delivered')
            <span class="badge badge--success" style="font-size: 0.75rem;">{{ __('panel.customer_panel.delivery_checklist') }}</span>
        @endif
    </div>
    <div class="grid grid--2">
        <div><div class="text-sm text-muted">Brand</div><div class="fw-700">{{ $lead->delivery_details['make'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Model</div><div class="fw-700">{{ $lead->delivery_details['model'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Shell Colour</div><div class="fw-700">{{ $lead->delivery_details['shell_colour'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Cabinet Colour</div><div class="fw-700">{{ $lead->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Accessories</div><div class="fw-700">{{ $lead->delivery_details['accessories'] ?? '—' }}</div></div>
        <div><div class="text-sm text-muted">Sale Price</div><div class="fw-700">{{ (isset($lead->delivery_details['sale_price'])) ? '£' . number_format($lead->delivery_details['sale_price'], 2) : '—' }}</div></div>
        <div><div class="text-sm text-muted">{{ __('panel.customer_panel.purchased_on') }}</div><div class="fw-700">{{ (isset($lead->delivery_details['delivery_date'])) ? \Carbon\Carbon::parse($lead->delivery_details['delivery_date'])->format('M d, Y') : '—' }}</div></div>
        <div>
            <div class="text-sm text-muted">{{ __('panel.customer_panel.documents') }}</div>
            <div class="fw-700" style="display:flex;gap:10px;margin-top:5px">
                @if($lead->invoice_path)
                    <a href="{{ \App\Support\PublicMedia::url($lead->invoice_path) }}" target="_blank" class="btn btn--ghost btn--sm">{{ __('panel.customer_panel.view_invoice') }}</a>
                @endif
                @if($lead->warranty_path)
                    <a href="{{ \App\Support\PublicMedia::url($lead->warranty_path) }}" target="_blank" class="btn btn--ghost btn--sm">{{ __('panel.customer_panel.view_warranty') }}</a>
                @endif
                @if(!$lead->invoice_path && !$lead->warranty_path)
                    —
                @endif
            </div>
        </div>
    </div>

    @php $leadChecks = isset($checklists) ? $checklists->where('lead_id', $lead->id) : collect(); @endphp
    @if($leadChecks->isNotEmpty())
    <div class="mt-4 pt-4" style="border-top:1px solid #e5e7eb">
        <div class="fw-700 mb-2" style="font-size:0.95rem;color:var(--gray-900)">{{ __('panel.customer_panel.delivery_service_checklist') }}</div>
        @foreach($leadChecks as $chk)
        <div class="flex justify-between align-center mb-2" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <div class="text-sm text-muted">{{ __('panel.customer_panel.recorded') }} {{ $chk->completed_at ? $chk->completed_at->format('d M Y') : $chk->created_at->format('d M Y') }} — {{ $chk->dealer?->businessDisplayName() ?: 'Dealer' }}</div>
            @if(!$chk->customer_signature)
                <button type="button" class="btn btn--primary btn--sm" onclick="openHtSignModal({{ $chk->id }})">{{ __('panel.customer_panel.sign_off') }}</button>
            @else
                <span class="badge badge--success">Signed</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@empty
<div class="card">
    <div class="text-center text-muted py-4">{{ __('panel.customer_panel.ownership_none') }}</div>
</div>
@endforelse

<div id="htSignatureModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:400px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" onclick="closeHtSignModal()">&times;</button>
        <h3 style="margin-top:0;font-weight:800">{{ __('panel.customer_panel.sign_to_confirm') }}</h3>
        <p class="text-sm text-muted">{{ __('panel.customer_panel.sign_to_confirm_sub') }}</p>
        <div style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:12px;background:#f9fafb">
            <canvas id="htSigCanvas" width="360" height="150" style="cursor:crosshair;max-width:100%;height:auto"></canvas>
        </div>
        <div class="modal-actions" style="justify-content:space-between">
            <button type="button" class="btn btn--ghost btn--sm" onclick="clearHtSig()">{{ __('panel.common.clear') }}</button>
            <div style="display:flex;gap:10px">
                <button type="button" class="btn btn--ghost btn--sm" onclick="closeHtSignModal()">{{ __('panel.common.cancel') }}</button>
                <button type="button" class="btn btn--primary btn--sm" onclick="saveHtSignature()">{{ __('panel.customer_panel.save_signature') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let htChecklistId = null;
const htCanvas = document.getElementById('htSigCanvas');
const htCtx = htCanvas ? htCanvas.getContext('2d') : null;
let htDrawing = false;

function openHtSignModal(id) {
    htChecklistId = id;
    document.getElementById('htSignatureModal').style.display = 'flex';
    clearHtSig();
}

function closeHtSignModal() {
    document.getElementById('htSignatureModal').style.display = 'none';
}

function clearHtSig() {
    if (htCtx && htCanvas) htCtx.clearRect(0, 0, htCanvas.width, htCanvas.height);
}

htCanvas?.addEventListener('mousedown', (e) => { htDrawing = true; htCtx.beginPath(); htCtx.moveTo(e.offsetX, e.offsetY); });
htCanvas?.addEventListener('mousemove', (e) => { if(htDrawing) { htCtx.lineTo(e.offsetX, e.offsetY); htCtx.stroke(); } });
htCanvas?.addEventListener('mouseup', () => htDrawing = false);

async function saveHtSignature() {
    if (!htChecklistId) return;
    const signature = htCanvas.toDataURL('image/png');
    try {
        const res = await fetch(`/customer/service-history/${htChecklistId}/sign`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ signature })
        });
        const data = await res.json();
        if (res.ok && data.ok) window.location.reload();
        else alert('Unable to save signature.');
    } catch (e) { alert('Network error'); }
}
</script>
@endsection
