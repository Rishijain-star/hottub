@extends('layouts.customer')
@section('title', 'Request History – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Request History</h1><p class="panel-page-sub">View your past service and part requests</p></div>
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

<script>
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
                <div style="font-family:'Dancing Script', cursive; font-size:1.5rem; color:var(--gray-900)">${req.customer_signature || 'N/A'}</div>
            </div>
        `;
        document.getElementById('viewModal').style.display = 'flex';
    }
</script>
@endsection
