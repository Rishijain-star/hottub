@extends('layouts.admin')
@section('title', __('panel.admin.pages.services_edit.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.services_edit.title') }}</h1>
        <p class="panel-page-sub">{{ $item->name }}</p>
    </div>
    <a href="{{ route('admin.services.index') }}" class="btn">{{ __('panel.admin.common.back') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.services.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Service Name *</label>
                <input name="name" class="form-input" value="{{ old('name',$item->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">URL Slug</label>
                <input name="slug" class="form-input" value="{{ old('slug',$item->slug) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Typical Starting Price</label>
                <input name="price" type="number" step="0.01" min="0" class="form-input" value="{{ old('price',$item->price) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Service Image</label>
                <input type="file" name="image" class="form-input" accept="image/*">
                <div class="text-sm text-muted">Upload a new image to replace cover</div>
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
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="6">{{ old('description',$item->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">What's Included</label>
            <div id="includesListEdit">
                @php $incs = old('includes', is_array($item->includes)?$item->includes:[]); @endphp
                @if(empty($incs)) @php $incs = ['']; @endphp @endif
                @foreach($incs as $inc)
                    <div class="input-group" style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="includes[]" value="{{ $inc }}" placeholder="Included item">
                        <button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn--ghost btn--sm" onclick="addIncludeEdit()">+ Add Item</button>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>
<div class="card" style="padding:0; margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Service</th><th>Price</th><th>Included</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $svc)
            <tr>
                <td><div class="fw-700 text-dark">{{ $svc->name }}</div><div class="text-sm text-muted">{{ $svc->slug }}</div></td>
                <td>@if(!is_null($svc->price)) £{{ number_format($svc->price,2) }} @else — @endif</td>
                <td>@if(is_array($svc->includes)) {{ count($svc->includes) }} items @else — @endif</td>
                <td>@if($svc->status==='active')<span class="badge badge--success">Active</span>@else<span class="badge badge--dark">Inactive</span>@endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.services.edit', $svc) }}" class="icon-btn" title="Edit">✎</a>
                        <button type="button"
                                class="icon-btn js-open-delete"
                                title="Delete"
                                data-action="{{ route('admin.services.destroy', $svc) }}"
                                data-entity="service">✕</button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
<script>
    function addIncludeEdit(){
        const row = document.createElement('div');
        row.className = 'input-group';
        row.style = 'display:flex;gap:8px;margin-bottom:8px';
        row.innerHTML = '<input class="form-input" name="includes[]" placeholder="Included item">' +
                        '<button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>';
        document.getElementById('includesListEdit').appendChild(row);
    }
</script>
@include('components.delete-confirm-modal')
@endsection
