@extends('layouts.admin')
@section('title', __('panel.admin.pages.outdoor_products_edit.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.outdoor_products_edit.title') }}</h1>
        <p class="panel-page-sub">{{ $item->brand }} — {{ $item->model }}</p>
    </div>
    <a href="{{ route('admin.outdoor-products.index') }}" class="btn">{{ __('panel.admin.common.back') }}</a>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('admin.outdoor-products.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Brand *</label>
                @if(isset($brands) && count($brands))
                <select name="brand_id" class="form-input">
                    <option value="">Select a brand...</option>
                    @foreach($brands as $b)
                        @php
                            $types = (array) ($b->types ?? []);
                            $legacy = $b->type ?? null;
                            $allowed = (
                                in_array('outdoor_kitchen', $types, true) ||
                                in_array('sauna', $types, true) ||
                                in_array('other', $types, true) ||
                                in_array('outdoor_products', $types, true) ||
                                in_array($legacy, ['outdoor_kitchen','sauna','other','outdoor_products'], true) ||
                                (empty($types) && empty($legacy)) ||
                                // Always keep currently selected brand visible for editing
                                ((int) $b->id === (int) $item->brand_id)
                            );
                        @endphp
                        @if($allowed)
                        <option value="{{ $b->id }}" @selected(old('brand_id',$item->brand_id)==$b->id)>{{ $b->name }}</option>
                        @endif
                    @endforeach
                </select>
                @else
                <input name="brand" class="form-input" value="{{ old('brand', $item->brand) }}" required>
                @endif
            </div>
            <div class="form-group"><label class="form-label">Model *</label><input name="model" class="form-input" value="{{ old('model', $item->model) }}" required></div>
            <div class="form-group">
                <label class="form-label">Product Type *</label>
                <input name="product_type" class="form-input" value="{{ old('product_type', $item->product_type) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tier</label>
                <select name="tier" class="form-input">
                    <option value="">Select...</option>
                    <option @selected(old('tier',$item->tier)=='Entry')>Entry</option>
                    <option @selected(old('tier',$item->tier)=='Mid Range')>Mid Range</option>
                    <option @selected(old('tier',$item->tier)=='Luxury')>Luxury</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Dimensions</label><input name="dimensions" class="form-input" value="{{ old('dimensions',$item->dimensions) }}"></div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    <option value="active" @selected(old('status',$item->status)=='active')>Active</option>
                    <option value="inactive" @selected(old('status',$item->status)=='inactive')>Inactive</option>
                </select>
            </div>
        </div>

        <div class="card" style="border:1px dashed var(--gray-200); background:#f8fafb; margin-top:1rem;">
            <div class="fw-800 mb-2" style="font-size:.95rem;color:var(--gray-900)">Expert Review Scores (0–5)</div>
            <div class="grid grid--3">
                <div class="form-group"><label class="form-label">Quality</label><input name="quality" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('quality',$item->quality) }}"></div>
                <div class="form-group"><label class="form-label">Durability</label><input name="durability" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('durability',$item->durability) }}"></div>
                <div class="form-group"><label class="form-label">Features</label><input name="features" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('features',$item->features) }}"></div>
                <div class="form-group"><label class="form-label">Value</label><input name="value" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('value',$item->value) }}"></div>
            </div>
        </div>

        <div class="grid grid--2" style="margin-top:1rem">
            <div class="form-group">
                <label class="form-label">Pros</label>
                <div id="prosListEdit">
                    @php $pros = old('pros', is_array($item->pros)?$item->pros:[]); @endphp
                    @foreach($pros as $p)
                        <div class="input-group" style="display:flex;gap:8px;margin-bottom:8px"><input class="form-input" name="pros[]" value="{{ $p }}" placeholder="Pro"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn--ghost btn--sm" onclick="addProEdit()">+ Add Pro</button>
            </div>
            <div class="form-group">
                <label class="form-label">Cons</label>
                <div id="consListEdit">
                    @php $cons = old('cons', is_array($item->cons)?$item->cons:[]); @endphp
                    @foreach($cons as $c)
                        <div class="input-group" style="display:flex;gap:8px;margin-bottom:8px"><input class="form-input" name="cons[]" value="{{ $c }}" placeholder="Con"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn--ghost btn--sm" onclick="addConEdit()">+ Add Con</button>
            </div>
        </div>

        <div class="form-group"><label class="form-label">Add Images</label><input type="file" name="images[]" class="form-input" accept="image/*" multiple><div class="text-sm text-muted">Upload to append. First image is cover.</div></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="4">{{ old('description', $item->description) }}</textarea></div>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button type="submit" class="btn btn--primary">Save Changes</button>
        </div>
    </form>
</div>
<script>
    function addProEdit(){const row=document.createElement('div');row.className='input-group';row.style='display:flex;gap:8px;margin-bottom:8px';row.innerHTML='<input class="form-input" name="pros[]" placeholder="Pro"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>';document.getElementById('prosListEdit').appendChild(row);}
    function addConEdit(){const row=document.createElement('div');row.className='input-group';row.style='display:flex;gap:8px;margin-bottom:8px';row.innerHTML='<input class="form-input" name="cons[]" placeholder="Con"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>';document.getElementById('consListEdit').appendChild(row);}
</script>
@endsection
