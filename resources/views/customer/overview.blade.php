@extends('layouts.customer')
@section('title', 'Dashboard – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Dashboard</h1><p class="panel-page-sub">Welcome back. Manage your hot tub and service requests</p></div>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="grid" style="display:grid;grid-template-columns:1fr;gap:2rem;align-items:start">
    @forelse($leads as $lead)
    <div class="product-section" style="border-bottom: 2px solid #e5e7eb; padding-bottom: 2rem; margin-bottom: 1rem;">
        <div class="fw-800 mb-3" style="font-size:1.25rem;color:var(--gray-900); display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $lead->delivery_details['make'] ?? 'Product' }} {{ $lead->delivery_details['model'] ?? '' }}</span>
            @if($lead->stage === 'Delivered')
                <span class="badge badge--success" style="font-size: 0.85rem;">Delivered</span>
            @endif
        </div>

        <div class="grid grid--2 mb-4" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Unit Information</div>
                <div class="grid grid--2">
                    <div><div class="text-sm text-muted">Brand</div><div class="fw-700">{{ $lead->delivery_details['make'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Model</div><div class="fw-700">{{ $lead->delivery_details['model'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Shell Colour</div><div class="fw-700">{{ $lead->delivery_details['shell_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Cabinet Colour</div><div class="fw-700">{{ $lead->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Accessories</div><div class="fw-700">{{ $lead->delivery_details['accessories'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Sale Price</div><div class="fw-700">{{ (isset($lead->delivery_details['sale_price'])) ? '£' . number_format($lead->delivery_details['sale_price'], 2) : '—' }}</div></div>
                </div>
            </div>

            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Dealer / Manufacturer</div>
                @if($lead->dealer)
                    <div class="fw-700" style="font-size:1.1rem; color:var(--primary-600)">{{ $lead->dealer->company_name ?: $lead->dealer->name }}</div>
                    <div class="text-sm text-muted mt-1">{{ $lead->dealer->email }}</div>
                    <div class="text-sm text-muted">{{ $lead->dealer->phone }}</div>
                    <div class="text-sm text-muted mt-2">{{ $lead->dealer->address }}</div>
                @else
                    <div class="text-muted italic">Not linked to a specific dealer</div>
                @endif
            </div>
        </div>

        @if($lead->dealer && $lead->packages->count() > 0)
        <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Maintenance Packages from {{ $lead->dealer->company_name ?: $lead->dealer->name }}</div>
        <div class="grid grid--3 mb-4">
            @foreach($lead->packages as $pkg)
            <div class="card" style="display:flex; flex-direction:column; margin-bottom: 0;">
                <div class="fw-800" style="font-size:1.05rem; color:var(--gray-900)">{{ $pkg->name }}</div>
                <div class="fw-700 mt-1" style="font-size:1.2rem; color:var(--primary-600)">£{{ number_format($pkg->price, 2) }}</div>
                <ul style="margin:10px 0; padding-left:18px; font-size:0.75rem; color:var(--gray-600); flex-grow:1">
                    @foreach($pkg->features ?? [] as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>
                <button class="btn btn--primary btn--sm w-100 mt-2" onclick='openPackageModal(@json($pkg), @json($lead->id))'>Select Plan</button>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="card">
        <div class="text-center text-muted py-4">No product details found yet.</div>
    </div>
    @endforelse
</div>

<div class="fw-800 mb-2 mt-4" style="font-size:1.05rem;color:var(--gray-900)">My Service Requests</div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead><tr><th>Request</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            @php
                $requests = \App\Models\ServiceRequest::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(5)->get();
            @endphp
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->product_name }}</td>
                    <td><span class="badge {{ $req->status === 'completed' ? 'badge--success' : '' }}">{{ ucfirst($req->status) }}</span></td>
                    <td>{{ $req->created_at->format('M d, Y') }}</td>
                    <td><a href="{{ route('customer.service-requests') }}" class="btn btn--ghost btn--sm">View</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No service requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Package Request Modal --}}
<div id="packageModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="document.getElementById('packageModal').style.display='none'">&times;</button>
        <h3 id="pkgModalTitle" style="margin-top:0; font-weight: 800;">Request Package</h3>
        
        <form method="POST" action="{{ route('customer.package-requests.store') }}">
            @csrf
            <input type="hidden" name="package_id" id="pkgIdInput">
            <input type="hidden" name="lead_id" id="leadIdInput">
            <div class="form-group">
                <label class="form-label">Additional Message (Optional)</label>
                <textarea name="message" class="form-input" rows="3" placeholder="Any specific requirements or questions?"></textarea>
            </div>
            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('packageModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--primary btn--sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPackageModal(pkg, leadId) {
    document.getElementById('pkgModalTitle').textContent = 'Request ' + pkg.name;
    document.getElementById('pkgIdInput').value = pkg.id;
    document.getElementById('leadIdInput').value = leadId;
    document.getElementById('packageModal').style.display = 'flex';
}
</script>
@endsection
