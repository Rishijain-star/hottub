@extends('layouts.admin')
@section('title', 'Edit Brand – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Edit Brand</h1>
        <p class="panel-page-sub">{{ $item->name }}</p>
    </div>
    <a href="{{ route('admin.brands.index') }}" class="btn">Back</a>
</div>
@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif
<div class="card">
    <form method="POST" action="{{ route('admin.brands.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-input" value="{{ old('name',$item->name) }}" required></div>
            <div class="form-group">
                <label class="form-label">Brand Type</label>
                <select name="type" class="form-input">
                    <option value="">Select...</option>
                    <option value="hot_tub" @selected(old('type',$item->type)=='hot_tub')>Hot Tub</option>
                    <option value="swim_spa" @selected(old('type',$item->type)=='swim_spa')>Swim Spa</option>
                    <option value="both" @selected(old('type',$item->type)=='both')>Both</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input" value="{{ old('website',$item->website) }}"></div>
            <div class="form-group"><label class="form-label">Country of Origin</label><input name="country_of_origin" class="form-input" value="{{ old('country_of_origin',$item->country_of_origin) }}"></div>
            <div class="form-group">
                <label class="form-label">Brand Logo</label>
                @if($item->logo_path)
                    <div style="margin-bottom:10px">
                        <img src="{{ asset('storage/'.$item->logo_path) }}" alt="Logo" style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:4px">
                    </div>
                @endif
                <input type="file" name="logo" class="form-input" accept="image/*">
            </div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="4">{{ old('description',$item->description) }}</textarea></div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="featured" value="1" @checked(old('featured',$item->featured))> Featured brand</label>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Logo</th><th>Name</th><th>Origin</th><th>Type</th><th>Website</th><th>Featured</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($brands as $b)
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
                <td>{{ $b->type ? str_replace('_',' ',ucfirst($b->type)) : '—' }}</td>
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
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $brands->links('components.pagination') }}</div>
</div>
@endsection
