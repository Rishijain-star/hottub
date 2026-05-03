@extends('layouts.customer')
@section('title', 'Service History – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Service History</h1><p class="panel-page-sub">Completed service and part requests</p></div>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Status</th>
                <th>Resolved On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->product_name }}</div>
                    <div class="text-sm text-muted">{{ ucwords($req->type) }} Request</div>
                </td>
                <td><span class="badge badge--success">Completed</span></td>
                <td>{{ $req->completed_at ? $req->completed_at->format('d M Y') : '—' }}</td>
                <td>
                    <button class="btn btn--ghost btn--sm" onclick="viewRequest({{ json_encode($req) }})">View</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:2rem">No past requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- View Modal --}}
<div id="viewModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('viewModal').style.display='none'">&times;</button>
        <h3 id="modalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">Request Details</h3>
        <div id="modalBody" style="margin-bottom:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end;"><button class="btn btn--ghost btn--sm" onclick="document.getElementById('viewModal').style.display='none'">Close</button></div>
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

    function viewRequest(req) {
        document.getElementById('modalTitle').textContent = req.product_name;
        const data = req.checklist_data || {};
        document.getElementById('modalBody').innerHTML = `
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Work Completed by Dealer:</h4>
                <div class="text-sm text-muted">
                    <div style="margin-bottom:5px"><strong>Type:</strong> ${data.service_type || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Date:</strong> ${data.service_date || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Summary:</strong> ${data.work_summary || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Parts:</strong> ${data.parts_replaced || 'None'}</div>
                </div>
            </div>
            <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Your Review:</h4>
                <div class="text-sm text-muted">${req.customer_review || 'No review provided.'}</div>
            </div>
            <div>
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Your Signature:</h4>
                ${req.customer_signature ? `
                    <div style="cursor:pointer;" onclick="openImagePreview(${JSON.stringify(publicMediaUrlClient(req.customer_signature))})">
                        <img src=${JSON.stringify(publicMediaUrlClient(req.customer_signature))} alt="Signature" style="max-width: 200px; border: 1px solid #eee; border-radius: 4px;"/>
                        <p style="font-size:10px; color:var(--gray-500); margin-top:4px;">Click to enlarge</p>
                    </div>
                ` : '<div class="text-sm text-muted">N/A</div>'}
            </div>
        `;
        document.getElementById('viewModal').style.display = 'flex';
    }
</script>
@endsection
