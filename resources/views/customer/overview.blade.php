@extends('layouts.customer')
@section('title', 'Dashboard – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Dashboard</h1><p class="panel-page-sub">Welcome back. Manage your hot tub and service requests</p></div>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

@if($dealer && count($packages))
<div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Maintenance Packages from {{ $dealer->name }}</div>
<div class="grid grid--3 mb-4" style="margin-bottom: 2rem;">
    @foreach($packages as $pkg)
    <div class="card" style="display:flex; flex-direction:column;">
        <div class="fw-800" style="font-size:1.1rem; color:var(--gray-900)">{{ $pkg->name }}</div>
        <div class="fw-700 mt-1" style="font-size:1.4rem; color:var(--primary-600)">£{{ number_format($pkg->price, 2) }}</div>
        <ul style="margin:12px 0; padding-left:18px; font-size:0.82rem; color:var(--gray-600); flex-grow:1">
            @foreach($pkg->features ?? [] as $f)
                <li>{{ $f }}</li>
            @endforeach
        </ul>
        <button class="btn btn--primary btn--sm w-100 mt-2" onclick="openPackageModal({{ json_encode($pkg) }})">Select Package</button>
    </div>
    @endforeach
</div>
@endif

<div class="grid" style="display:grid;grid-template-columns:1.2fr .8fr;gap:1rem;align-items:start">
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Hot Tub Details</div>
        <div class="grid grid--2">
            <div><div class="text-sm text-muted">Brand</div><div class="fw-700">{{ $lead?->delivery_details['make'] ?? '—' }}</div></div>
            <div><div class="text-sm text-muted">Model</div><div class="fw-700">{{ $lead?->delivery_details['model'] ?? '—' }}</div></div>
            <div><div class="text-sm text-muted">Shell Colour</div><div class="fw-700">{{ $lead?->delivery_details['shell_colour'] ?? '—' }}</div></div>
            <div><div class="text-sm text-muted">Cabinet Colour</div><div class="fw-700">{{ $lead?->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
        </div>
    </div>
    <div class="card">
        <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Recent Service Requests</div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:.5rem">
            <li><span class="badge">Pending</span> Pump noise diagnosis</li>
            <li><span class="badge badge--success">Completed</span> Water quality check</li>
            <li><span class="badge">Pending</span> Heater inspection</li>
        </ul>
    </div>
</div>
<div class="card" style="padding:0;margin-top:1rem">
    <table class="table">
        <thead><tr><th>Request</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <tr><td>Pump noise diagnosis</td><td><span class="badge">Pending</span></td><td>—</td><td><button class="btn btn--ghost btn--sm">View</button></td></tr>
            <tr><td>Water quality check</td><td><span class="badge badge--success">Completed</span></td><td>—</td><td><button class="btn btn--ghost btn--sm">View</button></td></tr>
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
function openPackageModal(pkg) {
    document.getElementById('pkgModalTitle').textContent = 'Request ' + pkg.name;
    document.getElementById('pkgIdInput').value = pkg.id;
    document.getElementById('packageModal').style.display = 'flex';
}
</script>
@endsection
