@extends('layouts.admin')
@section('title', 'Parts – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Parts Management</h1><p class="panel-page-sub">Manage hot tub replacement parts catalog</p></div>
    <button class="btn btn--primary btn--pill" id="toggleAddPart"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Part</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ session('success') }}</div> @endif

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.parts') }}" class="panel-filter-form panel-filter-form--4">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name or part number..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Category</label>
            <select name="category" class="form-input">
                <option value="">All Categories</option>
                <option value="Pumps" {{ request('category') === 'Pumps' ? 'selected' : '' }}>Pumps</option>
                <option value="Heaters" {{ request('category') === 'Heaters' ? 'selected' : '' }}>Heaters</option>
                <option value="Filters" {{ request('category') === 'Filters' ? 'selected' : '' }}>Filters</option>
                <option value="Other" {{ request('category') === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('admin.parts') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card" id="addPartCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Part</div>
    <form method="POST" action="{{ route('admin.parts.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Part Name *</label>
                <input name="name" class="form-input" required placeholder="e.g., Circulation Pump 1.5HP">
            </div>
            <div class="form-group">
                <label class="form-label">URL Slug</label>
                <input name="slug" class="form-input" placeholder="Auto-generated from name if left blank">
            </div>
            <div class="form-group">
                <label class="form-label">Part Number</label>
                <input name="part_number" class="form-input" placeholder="e.g., PWP-15E">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-input">
                    <option value="">Select...</option>
                    <option>Pumps</option>
                    <option>Heaters</option>
                    <option>Filters</option>
                    <option>Controls</option>
                    <option>Jets</option>
                    <option>Covers</option>
                    <option>Chemicals</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price</label>
                <input name="price" type="number" step="0.01" min="0" class="form-input" placeholder="e.g., 95.00">
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Part Images</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple>
            <div class="text-sm text-muted">Click to upload images (up to 4). First image is cover.</div>
        </div>
        <div class="form-group">
            <label class="form-label">Description (HTML supported)</label>
            <textarea name="description" class="form-input" rows="4" placeholder="Enter part description with HTML formatting if needed..."></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Compatible Brands</label>
            <div class="grid grid--3">
                @foreach($brands as $b)
                    @php
                        $types = (array) ($b->types ?? []);
                        $legacy = $b->type ?? null;
                        // Parts are compatible with hot tubs/swim spas brand families.
                        $allowed = (
                            in_array('parts', $types, true) ||
                            in_array('other', $types, true) ||
                            in_array($legacy, ['parts', 'other'], true)
                        );
                    @endphp
                    @if($allowed)
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="checkbox" name="compatible_brand_ids[]" value="{{ $b->id }}"> {{ $b->name }}
                    </label>
                    @endif
                @endforeach
            </div>
        </div>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">Create Part</button>
        </div>
    </form>
    <script>
        document.getElementById('toggleAddPart')?.addEventListener('click', function(){
            const el = document.getElementById('addPartCard');
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });
    </script>
</div>

<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Image</th><th>Part Name</th><th>Part Number</th><th>Category</th><th>Price</th><th>Compatible Brands</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr>
                <td style="width:60px">
                    @php 
                        $img = (is_array($it->images) && count($it->images)) ? $it->images[0] : null; 
                        if ($img && !Str::startsWith($img, ['http://', 'https://'])) {
                            $img = \App\Support\PublicMedia::url($img);
                        }
                    @endphp
                    @if($img)
                        <img src="{{ $img }}" alt="{{ $it->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                    @else
                        <div style="width:48px;height:48px;border:1px dashed var(--gray-300);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--gray-400)">📷</div>
                    @endif
                </td>
                <td><div class="fw-700 text-dark">{{ $it->name }}</div><div class="text-sm text-muted">{{ $it->slug }}</div></td>
                <td>{{ $it->part_number ?? '—' }}</td>
                <td>@if($it->category)<span class="badge">{{ $it->category }}</span>@else — @endif</td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td>
                    @if(is_array($it->compatible_brand_ids) && count($it->compatible_brand_ids))
                        <div style="display:flex; flex-wrap:wrap; gap:4px; max-width:200px">
                            @foreach($it->compatible_brand_ids as $bid)
                                @php $bname = $brands->firstWhere('id', $bid)->name ?? null; @endphp
                                @if($bname)
                                    <span class="badge" style="font-size:10px; padding:2px 6px">{{ $bname }}</span>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">All brands</span>
                    @endif
                </td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.parts.edit', $it) }}" class="icon-btn" title="Edit">✎</a>
                        <button type="button"
                                class="icon-btn js-open-delete"
                                title="Delete"
                                data-action="{{ route('admin.parts.destroy', $it) }}"
                                data-entity="part">✕</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">No parts found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@include('components.delete-confirm-modal')
@endsection
