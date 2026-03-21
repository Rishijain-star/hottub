@extends('layouts.customer')
@section('title', 'Service Requests – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Service Requests</h1><p class="panel-page-sub">Create and manage your requests</p></div>
    <button class="btn btn--primary btn--pill" id="toggleNewRequest">+ New Request</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="newRequestCard" style="display:none;margin-bottom:20px">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Submit New Service Request</div>
    <form method="POST" action="{{ route('customer.service-requests.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Request Type *</label>
                <select name="type" id="requestType" class="form-input" required onchange="updateProductList()">
                    <option value="">Select Type...</option>
                    <option value="part">Part Inquiry</option>
                    <option value="service">Service Request</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Specific Item *</label>
                <select name="product_id" id="productId" class="form-input" required disabled>
                    <option value="">Select Item...</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Details / Message</label>
            <textarea name="message" class="form-input" rows="3" placeholder="Describe your issue or inquiry..."></textarea>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary">Submit Request</button>
            <button type="button" class="btn btn--ghost" onclick="document.getElementById('newRequestCard').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
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
                    @if($req->status === 'under_review')
                        <button class="btn btn--primary btn--sm" onclick="openConfirmationModal({{ json_encode($req) }})">Review & Sign</button>
                    @else
                        <button class="btn btn--ghost btn--sm" onclick="viewRequest({{ json_encode($req) }})">View</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:2rem">No active service requests.</td></tr>
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

{{-- Confirmation Modal --}}
<div id="confirmationModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:600px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none" 
                onclick="document.getElementById('confirmationModal').style.display='none'">&times;</button>
        <h3 style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800;">Service Confirmation</h3>
        
        <div id="checklistDetails" style="background:#f8fafb; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #e5e7eb">
            <h4 style="margin-top:0; font-size:0.95rem; color:var(--gray-900)">Work Completed:</h4>
            <div id="checklistContent" class="text-sm text-muted"></div>
        </div>

        <form id="confirmationForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Review / Feedback (Optional)</label>
                <textarea name="customer_review" class="form-input" rows="2" placeholder="How was the service?"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Digital Signature (Type your full name) *</label>
                <input name="customer_signature" class="form-input" required placeholder="Your full name as signature">
            </div>

            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('confirmationModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--primary">Confirm & Mark Completed</button>
            </div>
        </form>
    </div>
</div>

<script>
    const parts = @json($parts);
    const services = @json($services);

    document.getElementById('toggleNewRequest')?.addEventListener('click', () => {
        const el = document.getElementById('newRequestCard');
        el.style.display = el.style.display === 'none' ? '' : 'none';
    });

    function openConfirmationModal(req) {
        const form = document.getElementById('confirmationForm');
        form.action = '{{ route("customer.service-requests.confirm", ":id") }}'.replace(':id', req.id);
        
        const content = document.getElementById('checklistContent');
        const data = req.checklist_data || {};
        content.innerHTML = `
            <div style="margin-bottom:5px"><strong>Type:</strong> ${data.service_type || 'N/A'}</div>
            <div style="margin-bottom:5px"><strong>Date:</strong> ${data.service_date || 'N/A'}</div>
            <div style="margin-bottom:5px"><strong>Summary:</strong> ${data.work_summary || 'N/A'}</div>
            <div style="margin-bottom:5px"><strong>Parts:</strong> ${data.parts_replaced || 'None'}</div>
            <div style="margin-bottom:5px"><strong>Notes:</strong> ${data.notes || 'None'}</div>
        `;
        
        document.getElementById('confirmationModal').style.display = 'flex';
    }

    function updateProductList() {
        const type = document.getElementById('requestType').value;
        const sel = document.getElementById('productId');
        sel.innerHTML = '<option value="">Select Item...</option>';
        sel.disabled = !type;
        
        const list = type === 'part' ? parts : (type === 'service' ? services : []);
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            sel.appendChild(opt);
        });
    }

    function viewRequest(req) {
        document.getElementById('modalTitle').textContent = req.product_name;
        document.getElementById('modalBody').innerHTML = `
            <div style="margin-bottom:10px"><span class="fw-700">Type:</span> ${req.type.toUpperCase()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Status:</span> ${req.status.toUpperCase()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Submitted:</span> ${new Date(req.created_at).toLocaleDateString()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Message:</span><br>${req.message || 'No message provided.'}</div>
        `;
        document.getElementById('viewModal').style.display = 'flex';
    }
</script>
@endsection
