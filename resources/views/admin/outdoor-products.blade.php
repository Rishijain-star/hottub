@extends('layouts.admin')
@section('title', __('panel.admin.pages.outdoor_products_index.title') . ' – Admin Panel')
@section('content')

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.outdoor_products_index.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.outdoor_products_index.sub') }}</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddProduct">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ __('panel.admin.pages.outdoor_products_index.add_product') }}
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

{{-- ─── ADD PRODUCT FORM ─────────────────────────────────────────── --}}
<div class="card" id="addProductCard" style="display:{{ $errors->any() ? 'block' : 'none' }}">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Outdoor Product</div>

    <form id="addProductForm" method="POST" action="{{ route('admin.outdoor-products.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Brand + Model --}}
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Brand *</label>
                @if(isset($brands) && count($brands))
                    <select name="brand_id" class="form-input @error('brand_id') is-invalid @enderror" required>
                        <option value="">Select a brand...</option>
                        @foreach($brands as $b)
                            @php
                                $types = (array) ($b->types ?? []);
                                $legacy = $b->type ?? null;
                                $allowed = (
                                    in_array('outdoor_kitchen', $types, true) ||
                                    in_array('sauna', $types, true) ||
                                    in_array('other', $types, true) ||
                                    // Legacy compatibility from previous builder versions
                                    in_array('outdoor_products', $types, true) ||
                                    in_array($legacy, ['outdoor_kitchen','sauna','other','outdoor_products'], true) ||
                                    // If brand has no type metadata at all, keep it visible (backward compatible)
                                    (empty($types) && empty($legacy))
                                );
                            @endphp
                            @if($allowed)
                            <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                @else
                    <input name="brand" class="form-input @error('brand') is-invalid @enderror"
                           required placeholder="e.g., Outdoor Co" value="{{ old('brand') }}">
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Model *</label>
                <input name="model" class="form-input @error('model') is-invalid @enderror"
                       required placeholder="e.g., Premium Gazebo" value="{{ old('model') }}">
            </div>

            {{-- Product Type + Tier --}}
            <div class="form-group">
                <label class="form-label">Product Type *</label>
                <input name="product_type" class="form-input" required placeholder="e.g., Gazebo, Kitchen" value="{{ old('product_type', 'Outdoor Product') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Tier</label>
                <select name="tier" class="form-input">
                    <option value="">Select...</option>
                    @foreach(['Entry','Mid Range','Luxury'] as $t)
                        <option {{ old('tier') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Dimensions --}}
            <div class="form-group">
                <label class="form-label">Dimensions</label>
                <input name="dimensions" class="form-input"
                       placeholder="e.g., 3m x 3m" value="{{ old('dimensions') }}">
            </div>

            {{-- Status --}}
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        {{-- ─── Expert Review Scores ──────────────────────────────────── --}}
        <div class="card" style="border:1px dashed var(--gray-200);background:#f8fafb;margin-top:1rem;">
            <div class="fw-800 mb-2" style="font-size:.95rem;color:var(--gray-900)">Expert Review Scores (0–5)</div>

            <div class="grid grid--3">
                @foreach(['quality','durability','features','value'] as $score)
                    <div class="form-group">
                        <label class="form-label">{{ ucfirst($score) }}</label>
                        <input name="{{ $score }}" id="score_{{ $score }}"
                               step="0.1" min="0" max="5" type="number"
                               class="form-input score-field"
                               placeholder="e.g., 4.5"
                               value="{{ old($score) }}">
                    </div>
                @endforeach

                {{-- Auto-calculated overall --}}
                <div class="form-group">
                    <label class="form-label">Overall <span class="text-muted" style="font-weight:400">(auto)</span></label>
                    <input id="overallDisplay" name="overall" readonly
                           class="form-input" placeholder="—"
                           style="background:var(--gray-100);color:var(--gray-500);cursor:default"
                           value="{{ old('overall') }}">
                </div>
            </div>
        </div>

        {{-- ─── Pros & Cons ────────────────────────────────────────────── --}}
        <div class="grid grid--2" style="margin-top:1.25rem">
            <div class="form-group">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                    <label class="form-label" style="font-weight:700">Pros</label>
                    <button type="button" onclick="addPro()" class="btn btn--xs btn--success">+ Add</button>
                </div>
                <div id="prosList">
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="pros[]" placeholder="Pro #1">
                        <button type="button" onclick="this.parentElement.remove()" class="icon-btn">×</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                    <label class="form-label" style="font-weight:700">Cons</label>
                    <button type="button" onclick="addCon()" class="btn btn--xs btn--warning">+ Add</button>
                </div>
                <div id="consList">
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="cons[]" placeholder="Con #1">
                        <button type="button" onclick="this.parentElement.remove()" class="icon-btn">×</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Images ──────────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1rem">
            <label class="form-label">Images</label>
            <input type="file" name="images[]" id="imageInput"
                   class="form-input" accept="image/*" multiple
                   onchange="previewImages(this)">
            <div id="imagePreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>
        </div>

        {{-- ─── Description ─────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1rem">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="4"
                      placeholder="Product description...">{{ old('description') }}</textarea>
        </div>

        <div id="uploadProgress" style="display:none;height:10px;background:#e5e7eb;border-radius:6px;overflow:hidden;margin:10px 0">
            <div id="uploadProgressBar" style="height:100%;width:0%;background:#22c55e"></div>
        </div>
        <div id="multiUploadList" style="display:none;gap:8px;margin:10px 0"></div>
        <div id="ajaxMessage" class="alert" style="display:none"></div>

        <div class="modal-actions" style="justify-content:flex-start;margin-top:1rem">
            <button type="submit" class="btn btn--primary">+ Add Product</button>
            <button type="button" class="btn btn--ghost" onclick="closeForm()">Cancel</button>
        </div>
    </form>
</div>

{{-- ─── PRODUCTS TABLE ───────────────────────────────────────────────── --}}
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead>
            <tr>
                <th>Model</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Tier</th>
                <th>Overall</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
                <tr>
                    <td class="fw-700 text-dark" style="display:flex;align-items:center;gap:8px">
                        @php 
                            $thumb = ($it->images && count($it->images)) ? \App\Support\PublicMedia::url($it->images[0]) : null; 
                        @endphp
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="{{ $it->model }}" style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid var(--gray-200)">
                        @else
                            <div style="width:42px;height:42px;border-radius:6px;border:1px dashed var(--gray-300);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--gray-400)">📷</div>
                        @endif
                        <span>{{ $it->model }}</span>
                    </td>
                    <td>{{ $it->brand }}</td>
                    <td>{{ $it->product_type }}</td>
                    <td>{{ $it->tier ?? '—' }}</td>
                    <td>{{ $it->overall ?? '—' }}</td>
                    <td>
                        <span class="badge badge--{{ $it->status === 'active' ? 'success' : 'dark' }}">{{ ucfirst($it->status) }}</span>
                    </td>
                    <td>
                        <div class="actions-row">
                            <a href="{{ route('admin.outdoor-products.edit', $it) }}" class="icon-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <button type="button"
                                    class="icon-btn js-open-delete"
                                    title="Delete"
                                    data-action="{{ route('admin.outdoor-products.destroy', $it) }}"
                                    data-entity="product">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6v14m8-14v14M5 6l1-2h12l1 2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:2rem">No outdoor products yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>

<script>
    const addCard = document.getElementById('addProductCard');
    const CSRF = '{{ csrf_token() }}';

    document.getElementById('toggleAddProduct')?.addEventListener('click', () => {
        addCard.style.display = addCard.style.display === 'none' ? '' : 'none';
    });
    const closeForm = () => addCard.style.display = 'none';

    const scoreFields = document.querySelectorAll('.score-field');
    const overallInput = document.getElementById('overallDisplay');
    const calcOverall = () => {
        let sum = 0, count = 0;
        scoreFields.forEach(f => {
            const v = parseFloat(f.value);
            if (!isNaN(v) && v >= 0 && v <= 5) { sum += v; count++; }
        });
        overallInput.value = count > 0 ? (sum / count).toFixed(1) : '';
    };
    scoreFields.forEach(f => f.addEventListener('input', calcOverall));

    const addPro = () => {
        const div = document.createElement('div');
        div.style = 'display:flex;gap:8px;margin-bottom:8px';
        div.innerHTML = '<input class="form-input" name="pros[]" placeholder="New Pro"><button type="button" onclick="this.parentElement.remove()" class="icon-btn">×</button>';
        document.getElementById('prosList').appendChild(div);
    };
    const addCon = () => {
        const div = document.createElement('div');
        div.style = 'display:flex;gap:8px;margin-bottom:8px';
        div.innerHTML = '<input class="form-input" name="cons[]" placeholder="New Con"><button type="button" onclick="this.parentElement.remove()" class="icon-btn">×</button>';
        document.getElementById('consList').appendChild(div);
    };

    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        Array.from(input.files).slice(0, 10).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:6px;border:1.5px solid var(--gray-200)';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // Ajax handling similar to hot-tubs.blade.php
    const addForm = document.getElementById('addProductForm');
    const progressWrap = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');
    const ajaxMsg = document.getElementById('ajaxMessage');

    addForm.addEventListener('submit', function (e) {
        e.preventDefault();
        ajaxMsg.style.display = 'none';
        progressWrap.style.display = '';
        progressBar.style.width = '0%';
        const btn = addForm.querySelector('button[type="submit"]');
        btn.disabled = true;

        const fd = new FormData(addForm);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', addForm.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.addEventListener('progress', (ev) => {
            if (ev.lengthComputable) progressBar.style.width = Math.round((ev.loaded / ev.total) * 100) + '%';
        });

        xhr.onload = function () {
            btn.disabled = false;
            progressWrap.style.display = 'none';
            if (xhr.status >= 200 && xhr.status < 300) {
                const data = JSON.parse(xhr.responseText);
                if (data.ok) {
                    ajaxMsg.className = 'alert alert--success';
                    ajaxMsg.textContent = 'Outdoor product created.';
                    ajaxMsg.style.display = '';
                    const files = document.getElementById('imageInput').files;
                    if (files && files.length) {
                        uploadImagesIndividually(data.item.id, files);
                    } else {
                        location.reload();
                    }
                }
            } else {
                ajaxMsg.className = 'alert alert--danger';
                ajaxMsg.textContent = 'Error creating product.';
                ajaxMsg.style.display = '';
            }
        };
        xhr.send(fd);
    });

    function uploadImagesIndividually(id, files) {
        const list = document.getElementById('multiUploadList');
        list.style.display = 'grid';
        list.style.gridTemplateColumns = 'repeat(3, 1fr)';
        list.innerHTML = '';
        const baseUrl = '{{ route('admin.outdoor-products.images', ':id') }}'.replace(':id', id);
        Array.from(files).slice(0,10).forEach(file => {
            const fd = new FormData();
            fd.append('image', file);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', baseUrl, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
            xhr.onload = () => { if (list.querySelectorAll('.done').length === files.length) location.reload(); };
            xhr.send(fd);
        });
    }
</script>

@include('components.delete-confirm-modal')

@endsection
