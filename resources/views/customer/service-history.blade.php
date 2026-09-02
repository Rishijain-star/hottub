@extends('layouts.customer')
@section('title', __('panel.service_history.title').' – '.__('panel.customer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.service_history.title') }}</h1><p class="panel-page-sub">{{ __('panel.customer_panel.service_history_sub_customer') }}</p></div>
</div>

<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.common.date') }}</th>
                <th>{{ __('panel.common.dealer') }}</th>
                <th>{{ __('panel.service_history.checklist') }}</th>
                <th>{{ __('panel.service_history.notes') }}</th>
                <th>{{ __('panel.common.status') }}</th>
                <th>{{ __('panel.common.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $item)
            <tr>
                <td>{{ $item->completed_at ? $item->completed_at->format('d M Y') : $item->created_at->format('d M Y') }}</td>
                <td>{{ $item->dealer->businessDisplayName() }}</td>
                <td>
                    <div style="font-size:12px;color:var(--gray-600)">
                        @foreach($item->checklist_data as $key => $val)
                            @if($val)
                                <span class="badge badge--success" style="margin-right:4px;margin-bottom:4px">{{ ucwords(str_replace('_',' ',$key)) }}</span>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td style="max-width:200px" class="text-sm">{{ $item->dealer_notes ?? '—' }}</td>
                <td>
                    @if($item->customer_signature)
                        <span class="badge badge--success">{{ __('panel.service_history.signed') }}</span>
                    @else
                        <span class="badge badge--warning">{{ __('panel.service_history.pending_signature') }}</span>
                    @endif
                </td>
                <td>
                    @if(!$item->customer_signature)
                        <button class="btn btn--primary btn--sm" onclick="openSignatureModal({{ $item->id }})">{{ __('panel.customer_panel.sign_off_service') }}</button>
                    @else
                        <button class="btn btn--ghost btn--sm" disabled>{{ __('panel.service_history.completed') }}</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.common.no_service_records') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="signatureModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:400px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="closeSignatureModal()">&times;</button>
        <h3 style="margin-top:0; font-weight: 800; margin-bottom: 0.5rem;">{{ __('panel.customer_panel.sign_off_service') }}</h3>
        <p class="text-sm text-muted" style="margin-bottom: 1.5rem;">{{ __('panel.customer_panel.sign_off_service_sub') }}</p>
        <div style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:15px;background:#f9fafb">
            <canvas id="sigCanvas" width="360" height="150" style="cursor:crosshair"></canvas>
        </div>
        <div class="modal-actions" style="justify-content:space-between">
            <button class="btn btn--ghost btn--sm" onclick="clearSignature()">{{ __('panel.common.clear') }}</button>
            <div style="display:flex;gap:10px">
                <button class="btn btn--ghost btn--sm" onclick="closeSignatureModal()">{{ __('panel.common.cancel') }}</button>
                <button class="btn btn--primary btn--sm" onclick="saveSignature()">{{ __('panel.customer_panel.save_sign_off') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@php
    $serviceHistoryPanelI18n = [
        'unableSave' => __('panel.customer_panel.unable_save_signature'),
        'networkError' => __('panel.common.network_error'),
    ];
@endphp

@section('scripts')
<script>
const panelI18n = @json($serviceHistoryPanelI18n);
let currentChecklistId = null;
const canvas = document.getElementById('sigCanvas');
const ctx = canvas?.getContext('2d');
let drawing = false;

function openSignatureModal(id) {
    currentChecklistId = id;
    document.getElementById('signatureModal').style.display = 'flex';
    clearSignature();
}

function closeSignatureModal() {
    document.getElementById('signatureModal').style.display = 'none';
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

canvas?.addEventListener('mousedown', (e) => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
canvas?.addEventListener('mousemove', (e) => { if(drawing) { ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); } });
canvas?.addEventListener('mouseup', () => drawing = false);

async function saveSignature() {
    const signature = canvas.toDataURL();
    try {
        const res = await fetch(`/customer/service-history/${currentChecklistId}/sign`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ signature })
        });
        const data = await res.json();
        if (res.ok && data.ok) {
            window.location.reload();
        } else {
            alert(panelI18n.unableSave);
        }
    } catch(err) { alert(panelI18n.networkError); }
}
</script>
@endsection
