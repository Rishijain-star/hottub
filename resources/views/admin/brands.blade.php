@extends('layouts.admin')
@section('title', 'Brands – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Brands</h1>
        <p class="panel-page-sub">Manage hot tub brands</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddBrand">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Brand
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

<div class="card" id="addBrandCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Brand</div>
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input name="name" class="form-input" required placeholder="e.g., Hotspring">
            </div>
            <div class="form-group">
                <label class="form-label">Brand Type</label>
                <select name="type" class="form-input">
                    <option value="">Select...</option>
                    <option value="hot_tub">Hot Tub</option>
                    <option value="swim_spa">Swim Spa</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Website</label>
                <input name="website" class="form-input" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label class="form-label">Country of Origin</label>
                <input name="country_of_origin" class="form-input" placeholder="e.g., USA">
            </div>
            <div class="form-group">
                <label class="form-label">Brand Logo</label>
                <input type="file" name="logo" class="form-input" accept="image/*">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="4"></textarea>
        </div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="featured" value="1"> Featured brand
        </label>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">+ Create Brand</button>
        </div>
    </form>
    <script>
        document.getElementById('toggleAddBrand')?.addEventListener('click', function(){
            const el = document.getElementById('addBrandCard');
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });
    </script>
</div>

<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Name</th>
                <th>Origin</th>
                <th>Type</th>
                <th>Website</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($brands as $b)
            <tr>
                <td>
                    @if($b->logo_path)
                        <img src="{{ asset('storage/'.$b->logo_path) }}" alt="{{ $b->name }}" style="width:40px;height:40px;object-fit:contain;border-radius:4px">
                    @else
                        —
                    @endif
                </td>
                <td class="fw-700 text-dark">{{ $b->name }}</td>
                <td>{{ $b->country_of_origin ?? '—' }}</td>
                <td>{{ $b->type ? str_replace('_',' ', ucfirst($b->type)) : '—' }}</td>
                <td>@if($b->website)<a href="{{ $b->website }}" target="_blank">{{ $b->website }}</a>@else — @endif</td>
                <td>@if($b->featured)<span class="badge badge--success">Featured</span>@else <span class="badge">—</span> @endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.brands.edit', $b) }}" class="icon-btn" title="Edit">✎</a>
                        <form method="POST" action="{{ route('admin.brands.destroy', $b) }}" onsubmit="return confirm('Delete this brand?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn" title="Delete">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted">No brands yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $brands->links('components.pagination') }}</div>
</div>
@endsection
