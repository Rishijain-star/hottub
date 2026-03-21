@extends('layouts.dealer')
@section('title', 'Maintenance Packages – Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Maintenance Packages</h1><p class="panel-page-sub">Create and manage your service subscription tiers</p></div>
    <button class="btn btn--primary btn--pill" onclick="document.getElementById('addPackageCard').style.display='block'">+ Add Package</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="addPackageCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Package</div>
    <form method="POST" action="{{ route('dealer.maintenance-packages.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Package Name *</label><input name="name" class="form-input" placeholder="e.g., Premium Service" required></div>
            <div class="form-group"><label class="form-label">Price (£) *</label><input name="price" type="number" step="0.01" class="form-input" required></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="2"></textarea></div>
        <div class="form-group">
            <label class="form-label">Key Features (One per line)</label>
            <textarea id="featuresInput" class="form-input" rows="4" placeholder="Annual Checkup&#10;Chemical Starter Kit"></textarea>
            <div id="featuresContainer"></div>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary" type="submit" onclick="prepareFeatures()">Save Package</button>
            <button type="button" class="btn btn--ghost" onclick="document.getElementById('addPackageCard').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<div class="grid grid--3">
    @forelse($packages as $pkg)
    <div class="card">
        <div class="fw-800" style="font-size:1.25rem; color:var(--gray-900)">{{ $pkg->name }}</div>
        <div class="fw-700 mt-1" style="font-size:1.5rem; color:var(--primary-600)">£{{ number_format($pkg->price, 2) }}</div>
        <p class="text-sm text-muted mt-2">{{ $pkg->description }}</p>
        <ul style="margin:15px 0; padding-left:20px; font-size:0.88rem; color:var(--gray-600)">
            @foreach($pkg->features ?? [] as $f)
                <li>{{ $f }}</li>
            @endforeach
        </ul>
        <div class="mt-auto pt-3 border-top">
            <span class="badge badge--{{ $pkg->status === 'active' ? 'success' : 'dark' }}">{{ ucfirst($pkg->status) }}</span>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: span 3; text-align:center; padding:3rem">
        <p class="text-muted">No maintenance packages created yet.</p>
    </div>
    @endforelse
</div>

<script>
function prepareFeatures() {
    const lines = document.getElementById('featuresInput').value.split('\n').filter(l => l.trim() !== '');
    const container = document.getElementById('featuresContainer');
    container.innerHTML = '';
    lines.forEach(l => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'features[]';
        input.value = l.trim();
        container.appendChild(input);
    });
}
</script>
@endsection
