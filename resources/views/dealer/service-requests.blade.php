@extends('layouts.dealer')
@section('title', 'Service Requests – Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Service Requests</h1><p class="panel-page-sub">Manage incoming service and parts requests from your customers</p></div>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Request</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->customer->name }}</div>
                    <div class="text-sm text-muted">{{ $req->customer->email }}</div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->product_name }}</div>
                    <div class="text-sm text-muted">{{ ucwords($req->type) }} Request</div>
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
                        <button class="btn btn--ghost btn--sm" onclick="viewRequest({{ json_encode($req) }})">View</button>
                        
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('dealer.service-requests.update', $req) }}" style="display:inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="processing">
                            <button class="btn btn--primary btn--sm">Process</button>
                        </form>
                        @endif

                        @if($req->status === 'processing')
                        <button class="btn btn--success btn--sm" onclick="openChecklistModal({{ json_encode($req) }})">Complete</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">No active service requests.</td></tr>
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

{{-- Checklist Modal --}}
<div id="checklistModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:600px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('checklistModal').style.display='none'">&times;</button>
        <h3 style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800;">Service Completion Checklist</h3>
        
        <form id="checklistForm" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="under_review">
            
            <div class="grid grid--2">
                <div class="form-group">
                    <label class="form-label">Service Type</label>
                    <input name="checklist[service_type]" class="form-input" required placeholder="e.g., Annual Maintenance">
                </div>
                <div class="form-group">
                    <label class="form-label">Service Date</label>
                    <input type="date" name="checklist[service_date]" class="form-input" required value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Work Done Summary</label>
                <textarea name="checklist[work_summary]" class="form-input" rows="3" required placeholder="Describe what was done..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Parts Replaced (if any)</label>
                <input name="checklist[parts_replaced]" class="form-input" placeholder="e.g., Filter, Pump Seal">
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Comments</label>
                <textarea name="checklist[notes]" class="form-input" rows="2" placeholder="Additional observations..."></textarea>
            </div>

            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('checklistModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--primary">Send to Customer for Review</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openChecklistModal(req) {
        const form = document.getElementById('checklistForm');
        form.action = '{{ route("dealer.service-requests.update", ":id") }}'.replace(':id', req.id);
        document.getElementById('checklistModal').style.display = 'flex';
    }

    function viewRequest(req) {
        document.getElementById('modalTitle').textContent = req.product_name;
        document.getElementById('modalBody').innerHTML = `
            <div style="margin-bottom:10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Customer:</span> ${req.customer.name}</div>
            <div style="margin-bottom:10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Type:</span> ${req.type.toUpperCase()}</div>
            <div style="margin-bottom:10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Status:</span> ${req.status.toUpperCase()}</div>
            <div style="margin-bottom:10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Submitted:</span> ${new Date(req.created_at).toLocaleDateString()}</div>
            <div style="margin-bottom:10px;"><span class="fw-700 text-dark">Message:</span><br><p class="text-sm text-muted" style="margin-top: 5px;">${req.message || 'No message provided.'}</p></div>
        `;
        document.getElementById('viewModal').style.display = 'flex';
    }
</script>
@endsection
