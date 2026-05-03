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
                <label class="form-label">Product *</label>
                <select name="lead_id" id="leadId" class="form-input" required onchange="updateDealerInfo()">
                    <option value="">Select your Product...</option>
                    @foreach($leads as $l)
                        <option value="{{ $l->id }}" data-dealer="{{ $l->dealer->company_name ?: $l->dealer->name }}">
                            {{ $l->delivery_details['make'] ?? 'Product' }} {{ $l->delivery_details['model'] ?? '' }} (Dealer: {{ $l->dealer->company_name ?: $l->dealer->name }})
                        </option>
                    @endforeach
                </select>
                <div id="dealerNotice" class="text-sm text-muted mt-1" style="display:none">
                    This request will be sent to: <strong id="dealerName" class="text-primary-600"></strong>
                </div>
            </div>
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
            <label class="form-label">Additional Details *</label>
            <textarea name="message" class="form-input" rows="3" placeholder="Describe your issue or inquiry..." required minlength="3"></textarea>
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
        <h3 id="viewModalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">Request Details</h3>
        <div id="viewModalBody" style="margin-bottom:20px"></div>
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

{{-- Confirmation Modal --}}
<div id="confirmationModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;padding:15px;">
    <div class="card" style="width:100%; max-width:600px; max-height:95vh; background:#fff; padding:25px; border-radius:12px; position:relative; overflow-y:auto; display:flex; flex-direction:column;">
        <button type="button" class="icon-btn" 
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none;z-index:10;" 
                onclick="document.getElementById('confirmationModal').style.display='none'">&times;</button>
        
        <h3 style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">Service Confirmation</h3>
        
        <div id="checklistDetails" style="background:#f8fafb; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #e5e7eb">
            <h4 style="margin-top:0; font-size:0.95rem; color:var(--gray-900)">Work Completed:</h4>
            <div id="checklistContent" class="text-sm text-muted"></div>
        </div>

        <form id="confirmationForm" method="POST" style="display:flex; flex-direction:column; gap:15px;">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Review / Feedback (Optional)</label>
                <textarea name="customer_review" class="form-input" rows="2" placeholder="How was the service?"></textarea>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Digital Signature *</label>
                <div id="signature-pad-container" style="border: 1px solid #ccc; border-radius: 0.25rem; height: 180px; width: 100%; overflow: hidden; background:#fff;">
                    <canvas id="signature-pad" style="width: 100%; height: 100%; cursor: crosshair; touch-action: none;"></canvas>
                </div>
                <input type="hidden" name="customer_signature">
                <button type="button" id="clear-signature" class="btn btn--sm btn--ghost mt-2">Clear</button>
            </div>

            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 5px; padding-bottom: 5px;">
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('confirmationModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--primary">Confirm & Mark Completed</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const parts = @json($parts);
    const services = @json($services);
    function openImagePreview(src) {
        document.getElementById('previewImage').src = src;
        document.getElementById('imagePreviewModal').style.display = 'flex';
    }

    let signaturePad;

    function resizeCanvas() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const w = canvas.offsetWidth;
        const h = canvas.offsetHeight;
        canvas.width = w * ratio;
        canvas.height = h * ratio;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
        if (signaturePad) {
            signaturePad.clear();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;

        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        window.addEventListener("resize", resizeCanvas);
        // Initial resize will happen when modal opens
        
        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('confirmationForm').addEventListener('submit', function (event) {
            if (signaturePad.isEmpty()) {
                alert("Please provide your signature.");
                event.preventDefault();
            } else {
                document.querySelector('input[name="customer_signature"]').value = signaturePad.toDataURL('image/png');
            }
        });
    });

    document.getElementById('toggleNewRequest')?.addEventListener('click', () => {
        const el = document.getElementById('newRequestCard');
        el.style.display = el.style.display === 'none' ? '' : 'none';
    });

    function updateDealerInfo() {
        const sel = document.getElementById('leadId');
        const opt = sel.options[sel.selectedIndex];
        const dealer = opt.getAttribute('data-dealer');
        const notice = document.getElementById('dealerNotice');
        const nameSpan = document.getElementById('dealerName');
        
        if (dealer) {
            nameSpan.textContent = dealer;
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

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
        
        // Use a small timeout to ensure display:flex is applied before measuring dimensions
        setTimeout(() => {
            resizeCanvas();
        }, 100);
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

    function viewRequest(req) {
        document.getElementById('viewModalTitle').textContent = req.product_name;
        document.getElementById('viewModalBody').innerHTML = `
            <div style="margin-bottom:10px"><span class="fw-700">Type:</span> ${req.type.toUpperCase()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Status:</span> ${req.status.toUpperCase()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Submitted:</span> ${new Date(req.created_at).toLocaleDateString()}</div>
            <div style="margin-bottom:10px"><span class="fw-700">Message:</span><br>${req.message || 'No message provided.'}</div>
            <div style="margin-top:20px">
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
