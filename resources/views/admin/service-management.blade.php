@extends('layouts.admin')
@section('title', 'Service Management – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Service Management</h1>
        <p class="panel-page-sub">Monitor all service requests, processing status, and customer confirmations</p>
    </div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.service-management') }}" class="grid grid--3" style="align-items: flex-end; gap: 1rem;">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Customer name or email..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn--primary" style="flex: 1;">Filter</button>
            <a href="{{ route('admin.service-management') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Dealer</th>
                <th>Service Type</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->customer->name ?? 'Unknown' }}</div>
                    <div class="text-sm text-muted">{{ $req->customer->email ?? '' }}</div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->dealer->name ?? 'Not Assigned' }}</div>
                    <div class="text-sm text-muted">{{ $req->dealer->role ?? '' }}</div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->product_name }}</div>
                    <div class="text-sm text-muted">{{ ucwords($req->type) }}</div>
                </td>
                <td>
                    @if($req->status === 'pending')
                        <span class="badge">Pending</span>
                    @elseif($req->status === 'processing')
                        <span class="badge badge--warning">Processing</span>
                    @elseif($req->status === 'under_review')
                        <span class="badge badge--warning" style="background:#fef3c7;color:#92400e;border-color:#fde68a">Under Review</span>
                    @else
                        <span class="badge badge--success">Completed</span>
                    @endif
                </td>
                <td>{{ $req->created_at->format('d M Y') }}</td>
                <td>
                    <div class="actions-row">
                        <button class="btn btn--ghost btn--sm" onclick="viewServiceDetails({{ json_encode($req) }})">View</button>
                        <a href="{{ route('admin.service-management.download', $req) }}" target="_blank" class="btn btn--primary btn--sm">Download</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">No service records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem">{{ $requests->links('components.pagination') }}</div>
</div>

{{-- Detail Modal --}}
<div id="serviceModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:650px;background:#fff;padding:25px;border-radius:12px;position:relative;max-height:90vh;overflow-y:auto">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('serviceModal').style.display='none'">&times;</button>
        <h3 id="modalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">Service Report</h3>
        
        <div id="modalBody"></div>

        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('serviceModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<script>
    function viewServiceDetails(req) {
        document.getElementById('modalTitle').textContent = 'Service Report: ' + req.product_name;
        const data = req.checklist_data || {};
        
        let statusHtml = '';
        if(req.status === 'pending') statusHtml = '<span class="badge">Pending</span>';
        else if(req.status === 'processing') statusHtml = '<span class="badge badge--warning">Processing</span>';
        else if(req.status === 'under_review') statusHtml = '<span class="badge badge--warning" style="background:#fef3c7;color:#92400e;border-color:#fde68a">Under Review</span>';
        else statusHtml = '<span class="badge badge--success">Completed</span>';

        document.getElementById('modalBody').innerHTML = `
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; background: #f8fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div>
                    <div style="font-size: 12px; color: var(--gray-500); margin-bottom: 4px;">Customer</div>
                    <div class="fw-700">${req.customer ? req.customer.name : 'Unknown'}</div>
                    <div class="text-sm">${req.customer ? req.customer.email : ''}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--gray-500); margin-bottom: 4px;">Dealer</div>
                    <div class="fw-700">${req.dealer ? req.dealer.name : 'Not Assigned'}</div>
                    <div class="text-sm">${req.dealer ? req.dealer.email : ''}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--gray-500); margin-bottom: 4px;">Status</div>
                    <div>${statusHtml}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--gray-500); margin-bottom: 4px;">Requested On</div>
                    <div class="fw-700">${new Date(req.created_at).toLocaleDateString()}</div>
                </div>
            </div>

            <div style="margin-bottom:20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Work Checklist (Dealer Input):</h4>
                <div class="text-sm text-muted">
                    <div style="margin-bottom:5px"><strong>Service Type:</strong> ${data.service_type || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Service Date:</strong> ${data.service_date || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Work Summary:</strong><br>${data.work_summary || 'N/A'}</div>
                    <div style="margin-bottom:5px"><strong>Parts Replaced:</strong> ${data.parts_replaced || 'None'}</div>
                    <div style="margin-bottom:5px"><strong>Dealer Notes:</strong> ${data.notes || 'None'}</div>
                </div>
            </div>

            <div style="margin-bottom:20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Customer Confirmation:</h4>
                <div class="text-sm text-muted">
                    <div style="margin-bottom:10px"><strong>Review:</strong> ${req.customer_review || 'No review provided.'}</div>
                    <div><strong>Digital Signature:</strong></div>
                    <div style="font-family:'Dancing Script', cursive; font-size:1.8rem; color:var(--gray-900); margin-top: 5px;">${req.customer_signature || 'Not signed yet'}</div>
                    <div style="font-size: 11px; margin-top: 5px;">Completed On: ${req.completed_at ? new Date(req.completed_at).toLocaleString() : 'Pending'}</div>
                </div>
            </div>
        `;
        document.getElementById('serviceModal').style.display = 'flex';
    }
</script>
@endsection
