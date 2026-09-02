@extends('layouts.customer')
@section('title', __('panel.nav.request_history').' – '.__('panel.customer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.nav.request_history') }}</h1><p class="panel-page-sub">{{ __('panel.request_history_sub') }}</p></div>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.common.request') }}</th>
                <th>{{ __('panel.common.status') }}</th>
                <th>{{ __('panel.common.resolved_on') }}</th>
                <th>{{ __('panel.common.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->product_name }}</div>
                    <div class="text-sm text-muted">{{ ucwords($req->type) }} {{ __('panel.common.request') }}</div>
                </td>
                <td><span class="badge badge--success">{{ __('panel.status.completed') }}</span></td>
                <td>{{ $req->completed_at ? $req->completed_at->format('d M Y') : '—' }}</td>
                <td>
                    <button class="btn btn--ghost btn--sm" onclick="viewRequest({{ json_encode($req) }})">{{ __('panel.common.view') }}</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.no_past_requests') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="viewModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('viewModal').style.display='none'">&times;</button>
        <h3 id="modalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">{{ __('panel.common.request_details') }}</h3>
        <div id="modalBody" style="margin-bottom:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end;"><button class="btn btn--ghost btn--sm" onclick="document.getElementById('viewModal').style.display='none'">{{ __('panel.common.close') }}</button></div>
    </div>
</div>

<div id="imagePreviewModal" class="modal" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.8);align-items:center;justify-content:center" onclick="this.style.display='none'">
    <div style="position:relative; max-width:90%; max-height:90%;">
        <button type="button" style="position:absolute; top:-40px; right:0; background:none; border:none; color:#fff; font-size:30px; cursor:pointer;">&times;</button>
        <img id="previewImage" src="" style="width:100%; height:auto; border-radius:8px; background:#fff;">
    </div>
</div>

@php
    $requestHistoryPanelI18n = [
        'workCompleted' => __('panel.customer_panel.work_completed_by_dealer'),
        'yourReview' => __('panel.your_review'),
        'yourSignature' => __('panel.your_signature'),
        'clickEnlarge' => __('panel.overview.click_to_enlarge'),
        'noReview' => __('panel.overview.no_review_provided'),
        'type' => __('panel.overview.type'),
        'summary' => __('panel.overview.summary'),
        'parts' => __('panel.overview.parts'),
        'date' => __('panel.service_requests.service_date'),
    ];
@endphp

<script>
const panelI18n = @json($requestHistoryPanelI18n);

function publicMediaUrlClient(rel) {
    if (!rel) return '';
    var s = String(rel).replace(/\\/g, '/').trim();
    s = s.replace(/\/storage\/app\/public\//gi, '/uploads/app/public/').replace(/\/storage\//gi, '/uploads/app/public/');
    s = s.replace(/\/uploads\/(?!app\/public\/)/gi, '/uploads/app/public/');
    if (/^https?:\/\//i.test(s)) return s;
    if (s.startsWith('/uploads/') || s.startsWith('/images/')) return s;
    s = s.replace(/^\/+/, '');
    var low = s.toLowerCase();
    while (low.indexOf('storage/app/public/') === 0) {
        s = s.substring(19);
        low = s.toLowerCase();
    }
    if (low.indexOf('public/storage/') === 0) s = s.substring(15);
    low = s.toLowerCase();
    if (low.indexOf('storage/') === 0 && low.indexOf('storage/app/') !== 0) s = s.substring(8);
    low = s.toLowerCase();
    while (low.indexOf('uploads/') === 0) { s = s.substring(8); low = s.toLowerCase(); }
    while (low.indexOf('app/public/') === 0) { s = s.substring(11); low = s.toLowerCase(); }
    if (low.indexOf('images/') === 0) return '/' + s;
    return '/uploads/app/public/' + s;
}

function openImagePreview(src) {
    document.getElementById('previewImage').src = src;
    document.getElementById('imagePreviewModal').style.display = 'flex';
}

function viewRequest(req) {
    document.getElementById('modalTitle').textContent = req.product_name;
    const data = req.checklist_data || {};
    document.getElementById('modalBody').innerHTML = `
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
            <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">${panelI18n.workCompleted}</h4>
            <div class="text-sm text-muted">
                <div style="margin-bottom:5px"><strong>${panelI18n.type}</strong> ${data.service_type || 'N/A'}</div>
                <div style="margin-bottom:5px"><strong>${panelI18n.date}</strong> ${data.service_date || 'N/A'}</div>
                <div style="margin-bottom:5px"><strong>${panelI18n.summary}</strong> ${data.work_summary || 'N/A'}</div>
                <div style="margin-bottom:5px"><strong>${panelI18n.parts}</strong> ${data.parts_replaced || 'None'}</div>
            </div>
        </div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
            <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">${panelI18n.yourReview}</h4>
            <div class="text-sm text-muted">${req.customer_review || panelI18n.noReview}</div>
        </div>
        <div>
            <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">${panelI18n.yourSignature}</h4>
            ${req.customer_signature ? `
                <div style="cursor:pointer;" onclick="openImagePreview(${JSON.stringify(publicMediaUrlClient(req.customer_signature))})">
                    <img src=${JSON.stringify(publicMediaUrlClient(req.customer_signature))} alt="Signature" style="max-width: 200px; border: 1px solid #eee; border-radius: 4px;"/>
                    <p style="font-size:10px; color:var(--gray-500); margin-top:4px;">${panelI18n.clickEnlarge}</p>
                </div>
            ` : '<div class="text-sm text-muted">N/A</div>'}
        </div>
    `;
    document.getElementById('viewModal').style.display = 'flex';
}
</script>
@endsection
