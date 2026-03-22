@extends('layouts.dealer')
@section('title', 'Maintenance Packages – Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Maintenance Packages</h1><p class="panel-page-sub">Create and manage your service subscription tiers</p></div>
    <button class="btn btn--primary btn--pill" onclick="showAddForm()">+ Add Package</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="packageFormCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)" id="formTitle">Add New Package</div>
    <form id="packageForm" method="POST" action="{{ route('dealer.maintenance-packages.store') }}">
        @csrf
        <div id="methodField"></div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Package Name *</label><input name="name" id="pkgName" class="form-input" placeholder="e.g., Premium Service" required></div>
            <div class="form-group"><label class="form-label">Price (£) *</label><input name="price" id="pkgPrice" type="number" step="0.01" class="form-input" required></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="pkgDesc" class="form-input" rows="2"></textarea></div>
        <div class="form-group">
            <label class="form-label">Key Features (One per line)</label>
            <textarea id="featuresInput" class="form-input" rows="4" placeholder="Annual Checkup&#10;Chemical Starter Kit"></textarea>
            <div id="featuresContainer"></div>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary" type="submit" onclick="prepareFeatures()">Save Package</button>
            <button type="button" class="btn btn--ghost" onclick="hideForm()">Cancel</button>
        </div>
    </form>
</div>

<div class="grid grid--3">
    @forelse($packages as $pkg)
    <div class="card" style="display:flex; flex-direction:column;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div class="fw-800" style="font-size:1.25rem; color:var(--gray-900)">{{ $pkg->name }}</div>
            <div style="display:flex; gap:8px;">
                <button class="icon-btn" onclick="editPackage({{ $pkg->id }})" title="Edit">✎</button>
                <form method="POST" action="{{ route('dealer.maintenance-packages.destroy', $pkg) }}" onsubmit="return confirm('Delete this package?')">
                    @csrf @method('DELETE')
                    <button class="icon-btn" style="color:var(--danger-600)">✕</button>
                </form>
            </div>
        </div>
        <div class="fw-700 mt-1" style="font-size:1.5rem; color:var(--primary-600)">£{{ number_format($pkg->price, 2) }}</div>
        <p class="text-sm text-muted mt-2">{{ $pkg->description }}</p>
        <ul style="margin:15px 0; padding-left:20px; font-size:0.88rem; color:var(--gray-600); flex-grow:1;">
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
function showAddForm() {
    document.getElementById('formTitle').textContent = 'Add New Package';
    document.getElementById('packageForm').action = "{{ route('dealer.maintenance-packages.store') }}";
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('pkgName').value = '';
    document.getElementById('pkgPrice').value = '';
    document.getElementById('pkgDesc').value = '';
    document.getElementById('featuresInput').value = '';
    document.getElementById('packageFormCard').style.display = 'block';
}

function hideForm() {
    document.getElementById('packageFormCard').style.display = 'none';
}

async function editPackage(id) {
    try {
        const res = await fetch(`/dealer/maintenance-packages/${id}/edit`);
        const data = await res.json();
        if (data.ok) {
            const pkg = data.package;
            document.getElementById('formTitle').textContent = 'Edit Package';
            document.getElementById('packageForm').action = `/dealer/maintenance-packages/${id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('pkgName').value = pkg.name;
            document.getElementById('pkgPrice').value = pkg.price;
            document.getElementById('pkgDesc').value = pkg.description || '';
            document.getElementById('featuresInput').value = (pkg.features || []).join('\n');
            document.getElementById('packageFormCard').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (err) { alert('Error loading package data'); }
}

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
