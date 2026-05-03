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
                <label class="form-label">Brand Type (legacy)</label>
                <select name="type" class="form-input">
                    <option value="">Select...</option>
                    <option value="hot_tub" @selected(old('type',$item->type)=='hot_tub')>Hot Tub</option>
                    <option value="swim_spa" @selected(old('type',$item->type)=='swim_spa')>Swim Spa</option>
                    <option value="both" @selected(old('type',$item->type)=='both')>Both</option>
                </select>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Brand Type</label>
                <div style="display:flex;flex-wrap:wrap;gap:12px">
                    @php $ts = old('types', $item->types ?? []); @endphp
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="hot_tub" @checked(in_array('hot_tub', (array)$ts))> Hot Tub</label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="swim_spa" @checked(in_array('swim_spa', (array)$ts))> Swim Spa</label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="both" @checked(in_array('both', (array)$ts))> Hot Tub &amp; Swim Spa</label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="outdoor_kitchen" @checked(in_array('outdoor_kitchen', (array)$ts))> Outdoor Kitchen</label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="sauna" @checked(in_array('sauna', (array)$ts))> Sauna</label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="types[]" value="other" @checked(in_array('other', (array)$ts))> Other</label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Brand Status</label>
                @php $brandStatus = old('is_active', (int)($item->is_active ?? 1)); @endphp
                <div style="display:flex;align-items:center;gap:16px">
                    <label class="form-check" style="display:flex;align-items:center;gap:6px">
                        <input type="radio" name="is_active" value="1" {{ (string)$brandStatus === '1' ? 'checked' : '' }}>
                        Active
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px">
                        <input type="radio" name="is_active" value="0" {{ (string)$brandStatus === '0' ? 'checked' : '' }}>
                        Inactive
                    </label>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input" value="{{ old('website',$item->website) }}"></div>
            <div class="form-group"><label class="form-label">Country of Origin</label><input name="country_of_origin" class="form-input" value="{{ old('country_of_origin',$item->country_of_origin) }}"></div>
            <div class="form-group">
                <label class="form-label">Brand Logo</label>
                @if($item->logo_path)
                    <div style="margin-bottom:10px">
                        <img src="{{ \App\Support\PublicMedia::url($item->logo_path) }}" alt="Logo" style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:4px">
                    </div>
                @endif
                <input type="file" name="logo" class="form-input" accept="image/*">
            </div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="4">{{ old('description',$item->description) }}</textarea></div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="featured" value="1" @checked(old('featured',$item->featured))> Featured brand</label>
        <p class="text-xs text-muted" style="margin-top:.35rem;max-width:42rem;">Only featured brands are shown on the homepage “Premium Brands We Feature” section.</p>
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
                        <img src="{{ \App\Support\PublicMedia::url($b->logo_path) }}" alt="{{ $b->name }}" style="width:40px;height:40px;object-fit:contain;border-radius:4px">
                    @else
                        —
                    @endif
                </td>
                <td class="fw-700 text-dark">{{ $b->name }}</td>
                <td>{{ $b->country_of_origin ?? '—' }}</td>
                <td>
                    @php
                        $labels = ['hot_tub' => 'Hot Tub', 'swim_spa' => 'Swim Spa', 'both' => 'Hot Tub & Swim Spa', 'outdoor_kitchen' => 'Outdoor Kitchen', 'sauna' => 'Sauna', 'other' => 'Other'];
                        $bits = [];
                        foreach ((array) ($b->types ?? []) as $t) { if (isset($labels[$t])) $bits[] = $labels[$t]; }
                    @endphp
                    @if(count($bits))
                        {{ implode(', ', $bits) }}
                    @elseif($b->type)
                        {{ str_replace('_',' ', ucfirst($b->type)) }}
                    @else
                        —
                    @endif
                </td>
                <td>@if($b->website)<a href="{{ $b->website }}" target="_blank">{{ $b->website }}</a>@else — @endif</td>
                <td>@if($b->featured)<span class="badge badge--success">Featured</span>@else <span class="badge">—</span> @endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.brands.edit', $b) }}" class="icon-btn" title="Edit">✎</a>
                        <button type="button"
                                class="icon-btn js-open-delete"
                                title="Delete"
                                data-action="{{ route('admin.brands.destroy', $b) }}"
                                data-entity="brand">✕</button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $brands->links('components.pagination') }}</div>
</div>
@include('components.delete-confirm-modal')
@endsection
