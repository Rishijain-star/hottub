@extends('layouts.manufacturer')
@section('title', __('panel.service_history.title').' - '.__('panel.manufacturer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.service_history.title') }}</h1><p class="panel-page-sub">{{ __('panel.service_history.sub') }}</p></div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('manufacturer.service-history') }}" class="panel-filter-form panel-filter-form--2">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.common.search_customer') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.common.filter') }}</button>
            <a href="{{ route('manufacturer.service-history') }}" class="btn btn--ghost">{{ __('panel.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

<div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.service_history.digital_checklists') }}</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.service_history.date') }}</th>
                <th>{{ __('panel.service_history.customer') }}</th>
                <th>{{ __('panel.service_history.checklist') }}</th>
                <th>{{ __('panel.service_history.notes') }}</th>
                <th>{{ __('panel.service_history.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $item)
            <tr>
                <td>{{ $item->completed_at ? $item->completed_at->format('d M Y') : $item->created_at->format('d M Y') }}</td>
                <td>
                    <div class="fw-700 text-dark">{{ $item->lead->name }}</div>
                    <div class="text-sm text-muted">{{ $item->lead->email }}</div>
                </td>
                <td>
                    <div style="display:flex; flex-wrap:wrap; gap:4px">
                        @foreach($item->checklist_data as $key => $val)
                            @if($val)
                                <span class="badge" style="font-size:10px;padding:2px 6px">{{ \App\Support\PanelTranslator::interestLabel($key) }}</span>
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
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.common.no_service_records') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($history->hasPages())
        <div style="padding:1rem">{{ $history->appends(request()->except('checklist_page'))->links('components.pagination') }}</div>
    @endif
</div>

<div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.service_history.service_part_requests') }}</div>
<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.service_history.date') }}</th>
                <th>{{ __('panel.service_history.customer') }}</th>
                <th>{{ __('panel.service_history.request') }}</th>
                <th>{{ __('panel.service_history.message') }}</th>
                <th>{{ __('panel.service_history.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completedRequests as $req)
            <tr>
                <td>{{ $req->completed_at ? $req->completed_at->format('d M Y') : $req->created_at->format('d M Y') }}</td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->customer->name }}</div>
                    <div class="text-sm text-muted">{{ $req->customer->email }}</div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->product_name }}</div>
                    <div class="text-sm text-muted">{{ \App\Support\PanelTranslator::interestLabel($req->type) }}</div>
                </td>
                <td style="max-width:200px" class="text-sm">
                    <button class="btn btn--ghost btn--xs" onclick="viewHistoryDetails({{ json_encode($req) }})">{{ __('panel.service_history.view_details') }}</button>
                </td>
                <td><span class="badge badge--success">{{ __('panel.service_history.completed') }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.service_history.no_completed_requests') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($completedRequests->hasPages())
        <div style="padding:1rem">{{ $completedRequests->appends(request()->except('request_page'))->links('components.pagination') }}</div>
    @endif
</div>

{{-- History Detail Modal --}}
<div id="historyModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:600px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('historyModal').style.display='none'">&times;</button>
        <h3 id="historyTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800;">{{ __('panel.service_history.service_history_details') }}</h3>
        
        <div id="historyBody"></div>

        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('historyModal').style.display='none'">{{ __('panel.common.close') }}</button>
        </div>
    </div>
</div>

{{-- Image Preview Modal --}}
<div id="imagePreviewModal" class="modal" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.8);align-items:center;justify-content:center" onclick="this.style.display='none'">
    <div style="position:relative; max-width:90%; max-height:90%;">
        <button type="button" style="position:absolute; top:-40px; right:0; background:none; border:none; color:#fff; font-size:30px; cursor:pointer;">&times;</button>
        <img id="previewImage" src="" style="width:100%; height:auto; border-radius:8px; background:#fff;">
    </div>
</div>

<script>
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

    function viewHistoryDetails(req) {
        document.getElementById('historyTitle').textContent = @json(__('panel.overview.service_history_for', ['name' => '___NAME___'])).replace('___NAME___', req.product_name);
        const data = req.checklist_data || {};
        document.getElementById('historyBody').innerHTML = `
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">{{ __('panel.overview.work_checklist') }}</h4>
                <div class="text-sm text-muted">
                    <div style="margin-bottom:5px"><strong>{{ __('panel.overview.type') }}</strong> ${data.service_type || '{{ __('panel.overview.n_a') }}'}</div>
                    <div style="margin-bottom:5px"><strong>{{ __('panel.overview.date') }}:</strong> ${data.service_date || '{{ __('panel.overview.n_a') }}'}</div>
                    <div style="margin-bottom:5px"><strong>{{ __('panel.overview.summary') }}</strong> ${data.work_summary || '{{ __('panel.overview.n_a') }}'}</div>
                    <div style="margin-bottom:5px"><strong>{{ __('panel.overview.parts') }}</strong> ${data.parts_replaced || '{{ __('panel.overview.none') }}'}</div>
                    <div style="margin-bottom:5px"><strong>{{ __('panel.overview.dealer_notes') }}</strong> ${data.notes || '{{ __('panel.overview.none') }}'}</div>
                </div>
            </div>
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">{{ __('panel.overview.customer_feedback') }}</h4>
                <div class="text-sm text-muted">${req.customer_review || '{{ __('panel.overview.no_review_provided') }}'}</div>
            </div>
            <div>
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">{{ __('panel.overview.customer_signature') }}</h4>
                ${req.customer_signature ? `
                    <div style="cursor:pointer;" onclick="openImagePreview(${JSON.stringify(publicMediaUrlClient(req.customer_signature))})">
                        <img src=${JSON.stringify(publicMediaUrlClient(req.customer_signature))} alt="{{ __('panel.overview.customer_signature') }}" style="max-width: 200px; border: 1px solid #eee; border-radius: 4px;"/>
                        <p style="font-size:10px; color:var(--gray-500); margin-top:4px;">{{ __('panel.overview.click_to_enlarge') }}</p>
                    </div>
                ` : '<div class="text-sm text-muted">{{ __('panel.overview.n_a') }}</div>'}
            </div>
        `;
        document.getElementById('historyModal').style.display = 'flex';
    }
</script>
@endsection
