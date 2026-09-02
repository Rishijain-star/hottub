@extends('layouts.dealer')
@section('title', __('panel.maintenance.title').' - '.__('panel.dealer_title'))
@section('content')
<style>
    .pkg-card {
        position: relative;
        display: flex;
        flex-direction: column;
        background: var(--white);
        border: 1.5px solid var(--gray-200);
        border-radius: 16px;
        padding: 1.75rem;
        transition: all 0.3s ease;
        height: 100%;
    }
    .pkg-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 168, 150, 0.1);
        border-color: var(--teal);
    }
    .pkg-card .pkg-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .pkg-card .pkg-name {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--gray-900);
        line-height: 1.2;
    }
    .pkg-card .pkg-price {
        font-size: 2rem;
        font-weight: 800;
        color: var(--teal);
        margin-bottom: 0.5rem;
    }
    .pkg-card .pkg-price span {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-400);
        margin-left: 2px;
    }
    .pkg-card .pkg-desc {
        color: var(--gray-500);
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }
    .pkg-card .pkg-features {
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem 0;
        flex-grow: 1;
    }
    .pkg-card .pkg-features li {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        color: var(--gray-600);
        font-size: 0.9rem;
    }
    .pkg-card .pkg-features li svg {
        color: var(--teal);
        flex-shrink: 0;
    }
    .pkg-card .pkg-footer {
        padding-top: 1.25rem;
        border-top: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.maintenance.title') }}</h1><p class="panel-page-sub">{{ __('panel.maintenance.sub') }}</p></div>
    <button class="btn btn--primary btn--pill" onclick="showAddForm()">{{ __('panel.maintenance.add_package') }}</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="packageFormCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)" id="formTitle">{{ __('panel.maintenance.add_new_package') }}</div>
    <form id="packageForm" method="POST" action="{{ route('dealer.maintenance-packages.store') }}">
        @csrf
        <div id="methodField"></div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.maintenance.package_name') }}</label><input name="name" id="pkgName" class="form-input" placeholder="e.g., Premium Service" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.maintenance.price') }}</label><input name="price" id="pkgPrice" type="number" step="0.01" class="form-input" required></div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.maintenance.plan_type') }}</label>
                <select name="plan_type" id="pkgPlanType" class="form-input" required>
                    <option value="monthly">{{ __('panel.customers.monthly') }}</option>
                    <option value="yearly">{{ __('panel.customers.yearly') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.maintenance.highlight') }}</label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:8px;">
                    <input type="checkbox" name="is_most_popular" id="pkgMostPopular" value="1">
                    {{ __('panel.maintenance.mark_most_popular') }}
                </label>
            </div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.maintenance.description') }}</label><textarea name="description" id="pkgDesc" class="form-input" rows="2"></textarea></div>
        <div class="form-group">
            <label class="form-label">{{ __('panel.maintenance.key_features') }}</label>
            <textarea id="featuresInput" class="form-input" rows="4" placeholder="{{ __('panel.maintenance.features_placeholder') }}"></textarea>
            <div id="featuresContainer"></div>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary" type="submit" onclick="prepareFeatures()">{{ __('panel.maintenance.save_package') }}</button>
            <button type="button" class="btn btn--ghost" onclick="hideForm()">{{ __('panel.common.cancel') }}</button>
        </div>
    </form>
</div>

<div class="grid grid--3">
    @forelse($packages as $pkg)
    <div class="pkg-card">
        @if($pkg->is_most_popular)
            <span class="badge badge--primary" style="position:absolute;top:10px;left:10px;">{{ __('panel.maintenance.most_popular') }}</span>
        @endif
        <div class="pkg-header">
            <div class="pkg-name">{{ $pkg->name }}</div>
            <div style="display:flex; gap:8px;">
                <button class="icon-btn" onclick="editPackage({{ $pkg->id }})" title="{{ __('panel.lead.edit') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
                <form method="POST" action="{{ route('dealer.maintenance-packages.destroy', $pkg) }}" onsubmit="event.preventDefault(); showConfirmationModal(this, '{{ __('panel.maintenance.delete_package_title') }}', '{{ __('panel.maintenance.delete_package_body') }}');">
                    @csrf @method('DELETE')
                    <button class="icon-btn" style="color:#ef4444; border-color: #fee2e2; background: #fef2f2;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="pkg-price">£{{ number_format($pkg->price, 2) }}<span>/{{ ($pkg->plan_type ?? 'yearly') === 'monthly' ? __('panel.maintenance.month') : __('panel.maintenance.year') }}</span></div>
        <div class="pkg-desc">{{ $pkg->description }}</div>
        
        <ul class="pkg-features">
            @foreach($pkg->features ?? [] as $f)
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    {{ $f }}
                </li>
            @endforeach
        </ul>

        <div class="pkg-footer">
            <span class="badge badge--{{ $pkg->status === 'active' ? 'success' : 'dark' }}">{{ ucfirst($pkg->status) }}</span>
            <div class="text-xs text-muted fw-600">{{ __('panel.maintenance.id', ['id' => str_pad($pkg->id, 4, '0', STR_PAD_LEFT)]) }}</div>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: span 3; text-align:center; padding:3rem">
        <p class="text-muted">{{ __('panel.maintenance.no_packages') }}</p>
    </div>
    @endforelse
</div>

<script>
function showAddForm() {
    document.getElementById('formTitle').textContent = @json(__('panel.maintenance.add_new_package'));
    document.getElementById('packageForm').action = "{{ route('dealer.maintenance-packages.store') }}";
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('pkgName').value = '';
    document.getElementById('pkgPrice').value = '';
    document.getElementById('pkgPlanType').value = 'monthly';
    document.getElementById('pkgMostPopular').checked = false;
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
            document.getElementById('formTitle').textContent = @json(__('panel.maintenance.edit_package'));
            document.getElementById('packageForm').action = `/dealer/maintenance-packages/${id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('pkgName').value = pkg.name;
            document.getElementById('pkgPrice').value = pkg.price;
            document.getElementById('pkgPlanType').value = pkg.plan_type || 'monthly';
            document.getElementById('pkgMostPopular').checked = !!pkg.is_most_popular;
            document.getElementById('pkgDesc').value = pkg.description || '';
            document.getElementById('featuresInput').value = (pkg.features || []).join('\n');
            document.getElementById('packageFormCard').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (err) { alert(@json(__('panel.maintenance.error_loading_package'))); }
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
