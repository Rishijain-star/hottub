@extends('layouts.admin')
@section('title', 'Edit Product – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Edit Product</h1>
        <p class="panel-page-sub">{{ $item->brand }} — {{ $item->model }}</p>
    </div>
    <a href="{{ route('admin.hot-tubs.index') }}" class="btn">Back</a>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('admin.hot-tubs.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Brand *</label>
                @if(isset($brands) && count($brands))
                <select name="brand_id" class="form-input">
                    <option value="">Select a brand...</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" @selected(old('brand_id',$item->brand_id)==$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
                @else
                <input name="brand" class="form-input" value="{{ old('brand', $item->brand) }}" required>
                @endif
            </div>
            <div class="form-group"><label class="form-label">Model *</label><input name="model" class="form-input" value="{{ old('model', $item->model) }}" required></div>
            <div class="form-group">
                <label class="form-label">Product Type *</label>
                <select name="product_type" class="form-input" required>
                    <option value="hot_tub" @selected(old('product_type',$item->product_type)=='hot_tub')>Hot Tub</option>
                    <option value="swim_spa" @selected(old('product_type',$item->product_type)=='swim_spa')>Swim Spa</option>
                </select>
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
            <div class="form-group"><label class="form-label">Seats</label><input type="number" name="seats" class="form-input" value="{{ old('seats',$item->seats) }}"></div>
            <div class="form-group"><label class="form-label">Jets</label><input type="number" name="jets" class="form-input" value="{{ old('jets',$item->jets) }}"></div>
            <div class="form-group"><label class="form-label">Dimensions</label><input name="dimensions" class="form-input" value="{{ old('dimensions',$item->dimensions) }}"></div>
            <div class="form-group"><label class="form-label">Power Requirements</label><input name="power_requirements" class="form-input" value="{{ old('power_requirements',$item->power_requirements) }}"></div>
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
                <div class="form-group"><label class="form-label">Comfort</label><input name="comfort" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('comfort',$item->comfort) }}"></div>
                <div class="form-group"><label class="form-label">Efficiency</label><input name="efficiency" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('efficiency',$item->efficiency) }}"></div>
                <div class="form-group"><label class="form-label">Features</label><input name="features" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('features',$item->features) }}"></div>
                <div class="form-group"><label class="form-label">Quality</label><input name="quality" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('quality',$item->quality) }}"></div>
                <div class="form-group"><label class="form-label">Value</label><input name="value" step="0.1" min="0" max="5" type="number" class="form-input" value="{{ old('value',$item->value) }}"></div>
            </div>
            <div class="text-sm text-muted">Overall score is auto-calculated from the 5 scores</div>
        </div>

        <div class="grid grid--2" style="margin-top:1rem">
            <div class="form-group">
                <label class="form-label">Pros</label>
                <div id="prosListEdit">
                    @php $pros = old('pros', is_array($item->pros)?$item->pros:[]); @endphp
                    @if(empty($pros)) @php $pros = ['']; @endphp @endif
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
                    @if(empty($cons)) @php $cons = ['']; @endphp @endif
                    @foreach($cons as $c)
                        <div class="input-group" style="display:flex;gap:8px;margin-bottom:8px"><input class="form-input" name="cons[]" value="{{ $c }}" placeholder="Con"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn--ghost btn--sm" onclick="addConEdit()">+ Add Con</button>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Current Images</label>
            <div id="existingImages" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:1rem">
                @if($item->images && count($item->images))
                    @foreach($item->images as $idx => $img)
                        <div class="image-item" style="position:relative;width:100px;height:100px;border:2px solid {{ $idx === 0 ? 'var(--primary)' : 'var(--gray-200)' }};border-radius:8px;overflow:hidden">
                            <img src="{{ asset('storage/'.$img) }}" style="width:100%;height:100%;object-fit:cover">
                            @if($idx === 0)
                                <div style="position:absolute;bottom:0;left:0;right:0;background:var(--primary);color:white;font-size:10px;text-align:center;padding:2px">Main</div>
                            @else
                                <button type="button" onclick="setMainImage({{ $idx }})" style="position:absolute;bottom:2px;left:2px;right:2px;background:rgba(255,255,255,0.9);border:none;border-radius:4px;font-size:10px;padding:2px;cursor:pointer">Set Main</button>
                            @endif
                            <button type="button" onclick="deleteImage({{ $idx }})" style="position:absolute;top:2px;right:2px;background:rgba(220,38,38,0.8);color:white;border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer">×</button>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No images uploaded.</p>
                @endif
            </div>
        </div>

        <div class="form-group"><label class="form-label">Add Images</label><input type="file" name="images[]" class="form-input" accept="image/*" multiple><div class="text-sm text-muted">Upload to append. First image is cover.</div></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="6">{{ old('description', $item->description) }}</textarea></div>
        <div class="modal-actions" style="justify-content:flex-start">
            <label style="display:flex;align-items:center;gap:6px;font-size:.88rem"><input type="checkbox" name="regen_slug"> Regenerate URL slug</label>
            <button type="submit" class="btn btn--primary">Save Changes</button>
        </div>
    </form>
</div>
<script>
    function addProEdit(){const row=document.createElement('div');row.className='input-group';row.style='display:flex;gap:8px;margin-bottom:8px';row.innerHTML='<input class="form-input" name="pros[]" placeholder="Pro"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>';document.getElementById('prosListEdit').appendChild(row);}
    function addConEdit(){const row=document.createElement('div');row.className='input-group';row.style='display:flex;gap:8px;margin-bottom:8px';row.innerHTML='<input class="form-input" name="cons[]" placeholder="Con"><button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>';document.getElementById('consListEdit').appendChild(row);}

    function setMainImage(index) {
        if (!confirm('Set this image as the main image?')) return;
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('index', index);
        
        fetch('{{ route('admin.hot-tubs.set-main-image', $item) }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) window.location.reload();
            else alert(data.msg || 'Error setting main image');
        });
    }

    function deleteImage(index) {
        if (!confirm('Delete this image?')) return;
        const url = '{{ route('admin.hot-tubs.delete-image', [$item->id, ':idx']) }}'.replace(':idx', index);
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('_method', 'DELETE');

        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) window.location.reload();
            else alert(data.msg || 'Error deleting image');
        });
    }
</script>

<div class="card" style="padding:0; margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Model</th><th>Brand</th><th>Type</th><th>Tier</th><th>Seats</th><th>Jets</th><th>Overall</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td class="fw-700 text-dark">{{ $it->model }}</td>
                <td>{{ $it->brand }}</td>
                <td>{{ str_replace('_',' ', ucfirst($it->product_type)) }}</td>
                <td>{{ $it->tier ?? '—' }}</td>
                <td>{{ $it->seats ?? '—' }}</td>
                <td>{{ $it->jets ?? '—' }}</td>
                <td>{{ $it->overall ?? '—' }}</td>
                <td>@if($it->status==='active')<span class="badge badge--success">Active</span>@else<span class="badge badge--dark">Inactive</span>@endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.hot-tubs.edit', $it) }}" class="icon-btn" title="Edit">✎</a>
                        <form method="POST" action="{{ route('admin.hot-tubs.destroy', $it) }}" onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn" title="Delete">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@endsection
