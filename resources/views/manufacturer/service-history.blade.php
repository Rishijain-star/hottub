@extends('layouts.manufacturer')
@section('title', 'Service History – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Service History</h1><p class="panel-page-sub">View all completed service records and customer signatures</p></div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('manufacturer.service-history') }}" class="panel-filter-form panel-filter-form--2">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Customer name or email..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('manufacturer.service-history') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Digital Service Checklists</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Checklist</th>
                <th>Notes</th>
                <th>Status</th>
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
                                <span class="badge" style="font-size:10px;padding:2px 6px">{{ ucwords(str_replace('_',' ',$key)) }}</span>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td style="max-width:200px" class="text-sm">{{ $item->dealer_notes ?? '—' }}</td>
                <td>
                    @if($item->customer_signature)
                        <span class="badge badge--success">Signed</span>
                    @else
                        <span class="badge badge--warning">Pending Signature</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">No service records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($history->hasPages())
        <div style="padding:1rem">{{ $history->appends(request()->except('checklist_page'))->links('components.pagination') }}</div>
    @endif
</div>

<div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Service & Part Requests</div>
<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Request</th>
                <th>Message</th>
                <th>Status</th>
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
                    <div class="text-sm text-muted">{{ ucwords($req->type) }}</div>
                </td>
                <td style="max-width:200px" class="text-sm">
                    <button class="btn btn--ghost btn--xs" onclick="viewHistoryDetails({{ json_encode($req) }})">View Details</button>
                </td>
                <td><span class="badge badge--success">Completed</span></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">No completed requests.</td></tr>
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
        <h3 id="historyTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800;">Service History Details</h3>
        
        <div id="historyBody"></div>

        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('historyModal').style.display='none'">Close</button>
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
        document.getElementById('historyTitle').textContent = 'Service History: ' + req.product_name;
        const data = req.checklist_data || {};
        document.getElementById('historyBody').innerHTML = `
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Work Checklist:</h4>
                <div class="text-sm text-muted">
                    <div style="margin-bottom:5px"><strong>Type:</strong> ${data.service_type || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Date:</strong> ${data.service_date || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Summary:</strong> ${data.work_summary || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Parts:</strong> ${data.parts_replaced || 'None'}</div>
                    <div style="margin-bottom:5px"><strong>Dealer Notes:</strong> ${data.notes || 'None'}</div>
                </div>
            </div>
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Customer Feedback:</h4>
                <div class="text-sm text-muted">${req.customer_review || 'No review provided.'}</div>
            </div>
            <div>
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Customer Signature:</h4>
                ${req.customer_signature ? `
                    <div style="cursor:pointer;" onclick="openImagePreview(${JSON.stringify(publicMediaUrlClient(req.customer_signature))})">
                        <img src=${JSON.stringify(publicMediaUrlClient(req.customer_signature))} alt="Signature" style="max-width: 200px; border: 1px solid #eee; border-radius: 4px;"/>
                        <p style="font-size:10px; color:var(--gray-500); margin-top:4px;">Click to enlarge</p>
                    </div>
                ` : '<div class="text-sm text-muted">N/A</div>'}
            </div>
        `;
        document.getElementById('historyModal').style.display = 'flex';
    }
</script>
@endsection
