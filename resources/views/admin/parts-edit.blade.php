@extends('layouts.admin')
@section('title', 'Edit Part – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Edit Part</h1>
        <p class="panel-page-sub">{{ $item->name }}</p>
    </div>
    <a href="{{ route('admin.parts') }}" class="btn">Back</a>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.parts.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Part Name *</label>
                <input name="name" class="form-input" value="{{ old('name',$item->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">URL Slug</label>
                <input name="slug" class="form-input" value="{{ old('slug',$item->slug) }}" placeholder="Auto-generated from name if left blank">
            </div>
            <div class="form-group">
                <label class="form-label">Part Number</label>
                <input name="part_number" class="form-input" value="{{ old('part_number',$item->part_number) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-input">
                    <option value="">Select...</option>
                    <option @selected(old('category',$item->category)=='Pumps')>Pumps</option>
                    <option @selected(old('category',$item->category)=='Heaters')>Heaters</option>
                    <option @selected(old('category',$item->category)=='Filters')>Filters</option>
                    <option @selected(old('category',$item->category)=='Controls')>Controls</option>
                    <option @selected(old('category',$item->category)=='Jets')>Jets</option>
                    <option @selected(old('category',$item->category)=='Covers')>Covers</option>
                    <option @selected(old('category',$item->category)=='Chemicals')>Chemicals</option>
                    <option @selected(old('category',$item->category)=='Other')>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price</label>
                <input name="price" type="number" step="0.01" min="0" class="form-input" value="{{ old('price',$item->price) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    <option value="active" @selected(old('status',$item->status)=='active')>Active</option>
                    <option value="inactive" @selected(old('status',$item->status)=='inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Add Images</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple>
            <div class="text-sm text-muted">Upload to append. First image is cover.</div>
        </div>
        <div class="form-group">
            <label class="form-label">Description (HTML supported)</label>
            <textarea name="description" class="form-input" rows="4">{{ old('description',$item->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Compatible Brands</label>
            <div class="grid grid--3">
                @php $compat = is_array($item->compatible_brand_ids) ? $item->compatible_brand_ids : []; @endphp
                @foreach($brands as $b)
                    @php
                        $types = (array) ($b->types ?? []);
                        $legacy = $b->type ?? null;
                        $allowed = (
                            in_array('parts', $types, true) ||
                            in_array('other', $types, true) ||
                            in_array($legacy, ['parts', 'other'], true) ||
                            // Always keep already-selected brands visible for editing
                            in_array($b->id, old('compatible_brand_ids', $compat), true)
                        );
                    @endphp
                    @if($allowed)
                    <label class="form-check" style="display:flex;align-items:center;gap:8px">
                        <input type="checkbox" name="compatible_brand_ids[]" value="{{ $b->id }}" @checked(in_array($b->id, old('compatible_brand_ids',$compat)))> {{ $b->name }}
                    </label>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Part Name</th><th>Part Number</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td><div class="fw-700 text-dark">{{ $it->name }}</div><div class="text-sm text-muted">{{ $it->slug }}</div></td>
                <td>{{ $it->part_number ?? '—' }}</td>
                <td>{{ $it->category ?? '—' }}</td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price,2) }} @else — @endif</td>
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
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@include('components.delete-confirm-modal')
@endsection
