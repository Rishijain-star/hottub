@extends('layouts.admin')
@section('title', 'Hot Tubs – Admin Panel')
@section('content')

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Hot Tubs & Swim Spas</h1>
        <p class="panel-page-sub">Manage product listings for both hot tubs and swim spas</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddProduct">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Product
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.hot-tubs.index') }}" class="panel-filter-form panel-filter-form--4">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Model or Brand..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Brand</label>
            <select name="brand_id" class="form-input">
                <option value="">All Brands</option>
                @foreach($brands as $b)
                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
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
            <a href="{{ route('admin.hot-tubs.index') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

{{-- ─── ADD PRODUCT FORM ─────────────────────────────────────────── --}}
<div class="card" id="addProductCard" style="display:{{ $errors->any() ? 'block' : 'none' }}">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Product</div>

    <form id="addProductForm" method="POST" action="{{ route('admin.hot-tubs.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Brand + Model --}}
        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Brand *</label>
                @if(isset($brands) && count($brands))
                    <select name="brand_id" id="hotTubBrandSelect" class="form-input @error('brand_id') is-invalid @enderror" required>
                        <option value="">Select a brand...</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input name="brand" class="form-input @error('brand') is-invalid @enderror"
                           required placeholder="e.g., Hotspring" value="{{ old('brand') }}">
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Model *</label>
                <input name="model" class="form-input @error('model') is-invalid @enderror"
                       required placeholder="e.g., Highlife Aria" value="{{ old('model') }}">
            </div>

            {{-- Product Type + Tier --}}
            <div class="form-group">
                <label class="form-label">Product Type *</label>
                <select name="product_type" id="hotTubProductTypeSelect" class="form-input" required>
                    <option value="hot_tub"  {{ old('product_type', 'hot_tub') === 'hot_tub'  ? 'selected' : '' }}>Hot Tub</option>
                    <option value="swim_spa" {{ old('product_type') === 'swim_spa' ? 'selected' : '' }}>Swim Spa</option>
                </select>
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

            {{-- Seats + Jets --}}
            <div class="form-group">
                <label class="form-label">Seats</label>
                <input name="seats" class="form-input" type="number" min="0"
                       placeholder="e.g., 5" value="{{ old('seats') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Jets</label>
                <input name="jets" class="form-input" type="number" min="0"
                       placeholder="e.g., 41" value="{{ old('jets') }}">
            </div>

            {{-- Dimensions + Power --}}
            <div class="form-group">
                <label class="form-label">Dimensions</label>
                <input name="dimensions" class="form-input"
                       placeholder="e.g., 221 × 221 × 91 cm" value="{{ old('dimensions') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Power Requirements</label>
                <input name="power_requirements" class="form-input"
                       placeholder="e.g., 240V, 50A" value="{{ old('power_requirements') }}">
            </div>

            {{-- Status --}}
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Homepage</label>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
                    <input type="checkbox" name="featured_on_homepage" value="1" {{ old('featured_on_homepage') ? 'checked' : '' }}>
                    Featured product (show in Featured Products on homepage when active)
                </label>
            </div>
        </div>

        {{-- ─── Expert Review Scores ──────────────────────────────────── --}}
        <div class="card" style="border:1px dashed var(--gray-200);background:#f8fafb;margin-top:1rem;">
            <div class="fw-800 mb-2" style="font-size:.95rem;color:var(--gray-900)">Expert Review Scores (0–5)</div>

            <div class="grid grid--3">
                @foreach(['comfort','efficiency','features','quality','value'] as $score)
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

            <p class="text-sm text-muted" style="margin-top:.5rem">
                Overall is auto-calculated as the average of the 5 scores above.
            </p>
        </div>

        {{-- ─── Pros ───────────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1.25rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                <label class="form-label" style="display:flex;align-items:center;gap:6px;margin:0;font-size:1rem;font-weight:700;color:var(--gray-900)">
                    <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                        <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                    </svg>
                    Pros
                </label>
                <button type="button" onclick="addPro()"
                    style="background:rgba(22,163,74,.1);color:#16a34a;border:none;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:600;cursor:pointer">
                    + Add Pro
                </button>
            </div>
            <div id="prosList">
                @if(old('pros'))
                    @foreach(old('pros') as $pro)
                        <div style="display:flex;gap:8px;margin-bottom:8px">
                            <input class="form-input" name="pros[]" placeholder="Pro #{{ $loop->iteration }}" value="{{ $pro }}">
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                        </div>
                    @endforeach
                @else
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="pros[]" placeholder="Pro #1">
                        <button type="button" onclick="this.parentElement.remove()"
                            style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Cons ───────────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                <label class="form-label" style="display:flex;align-items:center;gap:6px;margin:0;font-size:1rem;font-weight:700;color:var(--gray-900)">
                    <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/>
                        <path d="M17 2h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"/>
                    </svg>
                    Cons
                </label>
                <button type="button" onclick="addCon()"
                    style="background:rgba(217,119,6,.1);color:#d97706;border:none;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:600;cursor:pointer">
                    + Add Con
                </button>
            </div>
            <div id="consList">
                @if(old('cons'))
                    @foreach(old('cons') as $con)
                        <div style="display:flex;gap:8px;margin-bottom:8px">
                            <input class="form-input" name="cons[]" placeholder="Con #{{ $loop->iteration }}" value="{{ $con }}">
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                        </div>
                    @endforeach
                @else
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="cons[]" placeholder="Con #1">
                        <button type="button" onclick="this.parentElement.remove()"
                            style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Images ──────────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1rem">
            <label class="form-label">Images</label>
            <input type="file" name="images[]" id="imageInput"
                   class="form-input" accept="image/*" multiple
                   onchange="previewImages(this)">
            <p class="text-sm text-muted">Upload up to 10 images. First image is used as the cover.</p>
            <div id="imagePreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>
        </div>

        {{-- ─── Description ─────────────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1rem">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="6"
                      placeholder="Product description and features...">{{ old('description') }}</textarea>
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
                <th>Seats</th>
                <th>Jets</th>
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
                            $rawImgs = $it->images;
                            if ($rawImgs instanceof \Illuminate\Support\Collection) {
                                $rawImgs = $rawImgs->all();
                            }
                            $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                            $imgs = array_values(array_filter(array_map(function ($v) {
                                if (is_string($v)) return $v;
                                if (is_array($v)) return $v['path'] ?? $v['url'] ?? $v['file'] ?? ($v[0] ?? null);
                                return null;
                            }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                            $thumb = count($imgs) ? \App\Support\PublicMedia::url($imgs[0]) : null; 
                        @endphp
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="{{ $it->model }}" style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid var(--gray-200)">
                        @else
                            <div style="width:42px;height:42px;border-radius:6px;border:1px dashed var(--gray-300);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--gray-400)">📷</div>
                        @endif
                        <span>{{ $it->model }}</span>
                    </td>
                    <td>{{ $it->brand }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($it->product_type)) }}</td>
                    <td>{{ $it->tier ?? '—' }}</td>
                    <td>{{ $it->seats ?? '—' }}</td>
                    <td>{{ $it->jets ?? '—' }}</td>
                    <td>{{ $it->overall ?? '—' }}</td>
                    <td>
                        @if($it->status === 'active')
                            <span class="badge badge--success">Active</span>
                        @else
                            <span class="badge badge--dark">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-row">
                            <a href="{{ route('admin.hot-tubs.edit', $it) }}" class="icon-btn" title="Edit">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <button type="button"
                                    class="icon-btn js-open-delete"
                                    title="Delete"
                                    data-action="{{ route('admin.hot-tubs.destroy', $it) }}"
                                    data-entity="product">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6v14m8-14v14M5 6l1-2h12l1 2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-muted" style="text-align:center;padding:2rem">
                        No products yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>

<script>
    // ── Toggle form ──────────────────────────────────────────────────
    const addCard = document.getElementById('addProductCard');
    const CSRF = '{{ csrf_token() }}';

    document.getElementById('toggleAddProduct')?.addEventListener('click', function () {
        addCard.style.display = addCard.style.display === 'none' ? '' : 'none';
    });

    function closeForm() {
        addCard.style.display = 'none';
    }

    // ── Brand filtering by product type (Hot Tub vs Swim Spa) ─────────
    (function () {
        const brandSelect = document.getElementById('hotTubBrandSelect');
        const typeSelect = document.getElementById('hotTubProductTypeSelect');
        if (!brandSelect || !typeSelect) return;

        const BRAND_TYPE_MAP = @php
            $map = [];
            foreach ($brands as $b) {
                $types = (array) ($b->types ?? []);
                $legacy = $b->type ?? null;
                $forHot = in_array('hot_tub', $types, true) || in_array('both', $types, true) || in_array($legacy, ['hot_tub', 'both'], true);
                $forSwim = in_array('swim_spa', $types, true) || in_array('both', $types, true) || in_array($legacy, ['swim_spa', 'both'], true);
                $map[$b->id] = [
                    'hot_tub' => $forHot,
                    'swim_spa' => $forSwim,
                ];
            }
            echo json_encode($map);
        @endphp;

        function applyBrandFilter() {
            const type = typeSelect.value || 'hot_tub';
            const current = brandSelect.value;
            Array.from(brandSelect.options).forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const meta = BRAND_TYPE_MAP[opt.value] || null;
                const allowed = !meta || meta[type] || opt.value === current;
                opt.hidden = !allowed;
            });
            if (current && brandSelect.selectedOptions[0] && brandSelect.selectedOptions[0].hidden) {
                brandSelect.value = '';
            }
        }

        typeSelect.addEventListener('change', applyBrandFilter);
        applyBrandFilter();
    })();

    // ── Auto-calculate overall score ─────────────────────────────────
    const scoreFields = document.querySelectorAll('.score-field');
    const overallInput = document.getElementById('overallDisplay');

    function calcOverall() {
        let sum = 0, count = 0;
        scoreFields.forEach(f => {
            const v = parseFloat(f.value);
            if (!isNaN(v) && v >= 0 && v <= 5) { sum += v; count++; }
        });
        overallInput.value = count > 0 ? (sum / count).toFixed(2) : '';
    }

    scoreFields.forEach(f => f.addEventListener('input', calcOverall));

    // Run on load to restore calculated value after validation failure
    calcOverall();

    // ── Pros ─────────────────────────────────────────────────────────
    let prosCt = document.querySelectorAll('#prosList input').length;
    function addPro() {
        prosCt++;
        const row = document.createElement('div');
        row.style = 'display:flex;gap:8px;margin-bottom:8px';
        row.innerHTML = `<input class="form-input" name="pros[]" placeholder="Pro #${prosCt}">`
                      + `<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>`;
        document.getElementById('prosList').appendChild(row);
    }

    // ── Cons ─────────────────────────────────────────────────────────
    let consCt = document.querySelectorAll('#consList input').length;
    function addCon() {
        consCt++;
        const row = document.createElement('div');
        row.style = 'display:flex;gap:8px;margin-bottom:8px';
        row.innerHTML = `<input class="form-input" name="cons[]" placeholder="Con #${consCt}">`
                      + `<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>`;
        document.getElementById('consList').appendChild(row);
    }

    // ── Image preview ────────────────────────────────────────────────
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

    function publicMediaUrl(rel) {
        if (!rel) return '';
        var s = (typeof rel === 'object' && rel && rel.path) ? rel.path : rel;
        s = String(s).replace(/\\/g, '/').trim();
        s = s.replace(/\/storage\/app\/public\//gi, '/uploads/app/public/').replace(/\/storage\//gi, '/uploads/app/public/');
        s = s.replace(/\/uploads\/(?!app\/public\/)/gi, '/uploads/app/public/');
        if (/^https?:\/\//i.test(s)) return s;
        if (s.startsWith('/uploads/') || s.startsWith('/images/')) return s;
        s = s.replace(/^\/+/, '');
        var low = s.toLowerCase();
        while (low.indexOf('storage/app/public/') === 0) {
            s = s.substring(19);
            low = s.toLowerCase();
        }
        if (low.indexOf('public/storage/') === 0) s = s.substring(15);
        low = s.toLowerCase();
        if (low.indexOf('storage/') === 0 && low.indexOf('storage/app/') !== 0) s = s.substring(8);
        low = s.toLowerCase();
        while (low.indexOf('uploads/') === 0) { s = s.substring(8); low = s.toLowerCase(); }
        while (low.indexOf('app/public/') === 0) { s = s.substring(11); low = s.toLowerCase(); }
        if (low.indexOf('images/') === 0) return '/' + s;
        return '/uploads/app/public/' + s;
    }

    const addForm = document.getElementById('addProductForm');
    const progressWrap = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');
    const ajaxMsg = document.getElementById('ajaxMessage');
    addForm.addEventListener('submit', function (e) {
        e.preventDefault();
        ajaxMsg.style.display = 'none';
        ajaxMsg.className = 'alert';
        progressWrap.style.display = '';
        progressBar.style.width = '0%';
        const btn = addForm.querySelector('button[type=\"submit\"]');
        btn.disabled = true;
        const fd = new FormData();
        Array.from(addForm.elements).forEach(el=>{
            if (!el.name) return;
            if (el.type === 'file') return;
            if (el.type === 'checkbox') {
                if (el.checked) fd.append(el.name, el.value);
                return;
            }
            if (el.name.endsWith('[]')) {
                // For pros[] and cons[]
                fd.append(el.name, el.value);
            } else {
                fd.append(el.name, el.value);
            }
        });
        const xhr = new XMLHttpRequest();
        xhr.open('POST', addForm.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) {
                const pct = Math.round((ev.loaded / ev.total) * 100);
                progressBar.style.width = pct + '%';
            }
        });
        xhr.onload = function () {
            btn.disabled = false;
            progressWrap.style.display = 'none';
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data && data.ok && data.item) {
                        const it = data.item;
                        const img = Array.isArray(it.images) && it.images.length ? publicMediaUrl(it.images[0]) : '';
                        const badge = it.status === 'active' ? '<span class=\"badge badge--success\">Active</span>' : '<span class=\"badge badge--dark\">Inactive</span>';
                        const row = `
                        <tr>
                            <td class=\"fw-700 text-dark\" style=\"display:flex;align-items:center;gap:8px\">
                                ${img ? `<img src=\"${img}\" alt=\"${it.model}\" style=\"width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid var(--gray-200)\">` : ''}
                                <span>${it.model}</span>
                            </td>
                            <td>${it.brand ?? ''}</td>
                            <td>${(it.product_type || '').replace('_',' ')}</td>
                            <td>${it.tier ?? '—'}</td>
                            <td>${it.seats ?? '—'}</td>
                            <td>${it.jets ?? '—'}</td>
                            <td>${it.overall ?? '—'}</td>
                            <td>${badge}</td>
                            <td>
                                <div class=\"actions-row\">
                                    <a href=\"{{ url('/admin/hot-tubs') }}/${it.id}/edit\" class=\"icon-btn\" title=\"Edit\">
                                        <svg width=\"14\" height=\"14\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" viewBox=\"0 0 24 24\">
                                            <path d=\"m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z\"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                        const tbody = document.querySelector('.table tbody');
                        tbody.insertAdjacentHTML('afterbegin', row);
                        ajaxMsg.className = 'alert alert--success';
                        ajaxMsg.textContent = 'Hot tub created.';
                        ajaxMsg.style.display = '';
                        const files = document.getElementById('imageInput').files;
                        if (files && files.length) {
                            uploadImagesIndividually(it.id, files);
                        } else {
                            addForm.reset();
                            document.getElementById('imagePreview').innerHTML = '';
                        }
                    }
                } catch (e) {
                    ajaxMsg.className = 'alert alert--danger';
                    ajaxMsg.textContent = 'Unexpected response.';
                    ajaxMsg.style.display = '';
                }
            } else if (xhr.status === 422) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    let first = 'Validation error.';
                    if (res && res.errors) {
                        const keys = Object.keys(res.errors);
                        if (keys.length && res.errors[keys[0]].length) first = res.errors[keys[0]][0];
                    }
                    ajaxMsg.className = 'alert alert--danger';
                    ajaxMsg.textContent = first;
                    ajaxMsg.style.display = '';
                } catch (_) {
                    ajaxMsg.className = 'alert alert--danger';
                    ajaxMsg.textContent = 'Validation failed.';
                    ajaxMsg.style.display = '';
                }
            } else {
                ajaxMsg.className = 'alert alert--danger';
                ajaxMsg.textContent = 'Upload failed.';
                ajaxMsg.style.display = '';
            }
        };
        xhr.onerror = function(){
            btn.disabled = false;
            progressWrap.style.display = 'none';
            ajaxMsg.className = 'alert alert--danger';
            ajaxMsg.textContent = 'Network error.';
            ajaxMsg.style.display = '';
        };
        xhr.send(fd);
    });

    function uploadImagesIndividually(id, files) {
        const list = document.getElementById('multiUploadList');
        list.style.display = 'grid';
        list.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
        list.innerHTML = '';
        const baseUrl = '{{ route('admin.hot-tubs.images', ':id') }}'.replace(':id', id);
        Array.from(files).slice(0,10).forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = 'card';
            item.style = 'padding:10px;border:1px solid var(--gray-200);border-radius:10px;display:flex;align-items:center;gap:10px';
            const imgEl = document.createElement('img');
            imgEl.style = 'width:56px;height:56px;border-radius:8px;object-fit:cover;border:1px solid var(--gray-200)';
            const reader = new FileReader();
            reader.onload = e => imgEl.src = e.target.result;
            reader.readAsDataURL(file);
            const barWrap = document.createElement('div');
            barWrap.style = 'flex:1;height:8px;background:#e5e7eb;border-radius:6px;overflow:hidden';
            const bar = document.createElement('div');
            bar.style = 'height:100%;width:0%;background:#0ea5a3';
            barWrap.appendChild(bar);
            const status = document.createElement('div');
            status.className = 'text-sm';
            status.style = 'color:#6b7280;width:56px;text-align:right';
            status.textContent = '0%';
            item.appendChild(imgEl);
            item.appendChild(barWrap);
            item.appendChild(status);
            list.appendChild(item);
            compressAndUpload(file, baseUrl, bar, status);
        });
        addForm.reset();
        document.getElementById('imagePreview').innerHTML = '';
    }

    function compressAndUpload(file, url, bar, status) {
        doUpload(file, file.name, url, bar, status);
    }

    function doUpload(blob, name, url, bar, status) {
        const fd = new FormData();
        const fileObj = blob instanceof File ? blob : new File([blob], name, { type: blob.type || 'application/octet-stream' });
        fd.append('image', fileObj);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) {
                const pct = Math.round((ev.loaded / ev.total) * 100);
                bar.style.width = pct + '%';
                status.textContent = pct + '%';
            }
        });
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                status.textContent = 'Done';
            } else {
                status.textContent = 'Error';
                bar.style.background = '#ef4444';
            }
        };
        xhr.onerror = function(){ status.textContent = 'Network'; bar.style.background = '#ef4444'; };
        xhr.send(fd);
    }

    
</script>

@include('components.delete-confirm-modal')

@endsection
