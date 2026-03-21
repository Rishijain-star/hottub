@extends('layouts.admin')
@section('title', 'Edit Featured Content – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Edit Featured Content</h1><p class="panel-page-sub">{{ $item->title ?? '—' }}</p></div>
    <a href="{{ route('admin.featured') }}" class="btn">Back</a>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.featured.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Content Type *</label>
                <select name="content_type" class="form-input" required>
                    <option value="product_of_month" @selected(old('content_type',$item->content_type)=='product_of_month')>Product of the Month</option>
                    <option value="delivery_of_week" @selected(old('content_type',$item->content_type)=='delivery_of_week')>Delivery of the Week</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-input">
                    <option value="">Select brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" @selected(old('brand_id',$item->brand_id)==$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hot Tub/Swim Spa</label>
                <select name="hot_tub_id" class="form-input">
                    <option value="">Select product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('hot_tub_id',$item->hot_tub_id)==$p->id)>{{ $p->brand }} — {{ $p->model }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input name="title" class="form-input" value="{{ old('title',$item->title) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Featured Image</label>
            <input type="file" name="image" class="form-input" accept="image/*">
            <div class="text-sm text-muted">Upload a new image to replace existing</div>
        </div>
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Featured From</label>
                <input type="date" name="featured_from" class="form-input" value="{{ old('featured_from', optional($item->featured_from)->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Featured Until</label>
                <input type="date" name="featured_until" class="form-input" value="{{ old('featured_until', optional($item->featured_until)->format('Y-m-d')) }}">
            </div>
        </div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="show_on_homepage" value="1" @checked(old('show_on_homepage',$item->show_on_homepage))> Display on homepage
        </label>
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-input" required>
                <option value="active" @selected(old('status',$item->status)=='active')>Active</option>
                <option value="inactive" @selected(old('status',$item->status)=='inactive')>Inactive</option>
            </select>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>
<div class="grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-top:1rem">
    @foreach($items as $it)
        <div class="card">
            <div style="display:flex;gap:14px">
                <div style="width:160px;height:100px;background:#f3f4f6;border:1px solid var(--gray-200);border-radius:10px;overflow:hidden;display:flex;align-items:center;justify-content:center">
                    @if($it->image_url)
                        <img src="{{ $it->image_url }}" alt="{{ $it->title }}" style="width:100%;height:100%;object-fit:cover">
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
    @endforeach
</div>
@endsection
