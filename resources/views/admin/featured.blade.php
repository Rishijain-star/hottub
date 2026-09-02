@extends('layouts.admin')
@section('title', __('panel.admin.pages.featured_index.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.pages.featured_index.title') }}</h1><p class="panel-page-sub">{{ __('panel.admin.pages.featured_index.sub') }}</p></div>
    <button class="btn btn--primary btn--pill" id="toggleAddFeatured"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> {{ __('panel.admin.pages.featured_index.add_featured') }}</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ session('error') }}</div> @endif

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.featured') }}" class="panel-filter-form panel-filter-form--4">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Title..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Type</label>
            <select name="content_type" class="form-input">
                <option value="">All Types</option>
                <option value="product_of_month" {{ request('content_type') === 'product_of_month' ? 'selected' : '' }}>Product of the Month</option>
                <option value="delivery_of_week" {{ request('content_type') === 'delivery_of_week' ? 'selected' : '' }}>Delivery of the Week</option>
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
            <a href="{{ route('admin.featured') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card" id="addFeaturedCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add Featured Content</div>
    <form method="POST" action="{{ route('admin.featured.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Content Type *</label>
                <select name="content_type" class="form-input" required>
                    <option value="product_of_month">Product of the Month</option>
                    <option value="delivery_of_week">Delivery of the Week</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-input">
                    <option value="">Select brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hot Tub/Swim Spa</label>
                <select name="hot_tub_id" class="form-input">
                    <option value="">Select product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->brand }} — {{ $p->model }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input name="title" class="form-input" placeholder="Auto-generated from product/dealer if left blank">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="4" placeholder="Add a short supporting description shown below the featured title on the homepage">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Featured From</label>
                <input type="date" name="featured_from" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Featured Until</label>
                <input type="date" name="featured_until" class="form-input">
            </div>
        </div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="show_on_homepage" value="1" checked> Display on homepage
        </label>
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-input" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Hero image (optional)</label>
            <input type="file" name="image" class="form-input" accept="image/*">
        </div>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">Create</button>
        </div>
    </form>
    <script>
        document.getElementById('toggleAddFeatured')?.addEventListener('click', function(){
            const el = document.getElementById('addFeaturedCard');
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });
    </script>
</div>

<div class="grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-top:1rem">
    @forelse($items as $it)
        <div class="card">
            <div style="display:flex;gap:14px">
                <div style="width:160px;height:100px;background:#f3f4f6;border:1px solid var(--gray-200);border-radius:10px;overflow:hidden;display:flex;align-items:center;justify-content:center">
                    @php
                        $adminImg = $it->image_url ? \App\Support\PublicMedia::url($it->image_url) : null;
                        if (!$adminImg && $it->hotTub) {
                            $rawImgs = $it->hotTub->images;
                            if ($rawImgs instanceof \Illuminate\Support\Collection) {
                                $rawImgs = $rawImgs->all();
                            }
                            $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                            $imgs = array_values(array_filter(array_map(function ($v) {
                                if (is_string($v)) return $v;
                                if (is_array($v)) return $v['path'] ?? $v['url'] ?? $v['file'] ?? ($v[0] ?? null);
                                return null;
                            }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                            if (count($imgs)) {
                                $adminImg = \App\Support\PublicMedia::url($imgs[0]);
                            }
                        }
                    @endphp
                    @if($adminImg)
                        <img src="{{ $adminImg }}" alt="{{ $it->title }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <span>📷</span>
                    @endif
                </div>
                <div style="flex:1">
                    <div class="fw-800" style="font-size:1.05rem;color:var(--gray-900)">
                        @if($it->content_type==='delivery_of_week') Delivery of Week @else Product of Month @endif
                    </div>
                    <div class="text-sm text-muted">{{ $it->title ?: '—' }}</div>
                    <div class="text-sm text-muted">{{ $it->featured_from }} — {{ $it->featured_until }}</div>
                    <div style="margin-top:6px">@if($it->status==='active')<span class="badge badge--success">Active</span>@else<span class="badge badge--dark">Inactive</span>@endif</div>
                    <div class="actions-row" style="margin-top:8px">
                        <a href="{{ route('admin.featured.edit', $it) }}" class="btn btn--ghost btn--sm">Edit</a>
                        <form method="POST" action="{{ route('admin.featured.destroy', $it) }}" onsubmit="return confirm('Delete this featured item?')">@csrf @method('DELETE') <button class="btn btn--ghost btn--sm">Delete</button></form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card"><div class="text-muted">No featured items yet.</div></div>
    @endforelse
</div>
<div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endsection
