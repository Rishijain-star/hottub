@extends('layouts.manufacturer')
@section('title', 'Follow-Up / Requests – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Follow-Up / Requests</h1><p class="panel-page-sub">Manage incoming package selections and maintenance requests</p></div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('manufacturer.package-requests') }}" class="grid grid--3" style="align-items: flex-end; gap: 1rem;">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Customer name or email..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="responded" {{ request('status') === 'responded' ? 'selected' : '' }}>Responded</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn--primary" style="flex: 1;">Filter</button>
            <a href="{{ route('manufacturer.package-requests') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Package Requested</th>
                <th>Price</th>
                <th>Status</th>
                <th>Date</th>
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
                    <div class="fw-700 text-dark">{{ $req->package->name }}</div>
                </td>
                <td class="fw-700 text-primary">£{{ number_format($req->package->price, 2) }}</td>
                <td>
                    @if($req->status === 'pending')
                        <span class="badge badge--warning">Urgent Follow-Up</span>
                    @elseif($req->status === 'responded')
                        <span class="badge badge--success">Responded</span>
                    @else
                        <span class="badge badge--dark">Closed</span>
                    @endif
                </td>
                <td>{{ $req->created_at->format('d M Y') }}</td>
                <td>
                    <div class="actions-row">
                        <button class="btn btn--ghost btn--sm" onclick="viewRequestDetails({{ json_encode($req) }})">View</button>
                        
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('manufacturer.package-requests.update', $req) }}" style="display:inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="responded">
                            <button class="btn btn--primary btn--xs">Mark Responded</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">No follow-up requests.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($requests->hasPages())
        <div style="padding:1rem">{{ $requests->links('components.pagination') }}</div>
    @endif
</div>

{{-- Detail Modal --}}
<div id="requestModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="document.getElementById('requestModal').style.display='none'">&times;</button>
        <h3 id="modalTitle" style="margin-top:0; font-weight: 800;">Request Details</h3>
        <div id="modalBody" style="margin-top:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('requestModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<script>
function viewRequestDetails(req) {
    document.getElementById('modalTitle').textContent = 'Package Request: ' + req.package.name;
    document.getElementById('modalBody').innerHTML = `
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Customer:</span> ${req.customer.name}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Email:</span> ${req.customer.email}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">Package:</span> ${req.package.name} (£${req.package.price})</div>
        <div style="margin-bottom:15px;"><span class="fw-700 text-dark">Message from Customer:</span><br><p class="text-sm text-muted" style="margin-top: 5px;">${req.message || 'No message provided.'}</p></div>
    `;
    document.getElementById('requestModal').style.display = 'flex';
}
</script>
@endsection
