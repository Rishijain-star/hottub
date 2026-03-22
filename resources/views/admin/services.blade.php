@extends('layouts.admin')
@section('title', 'Services – Admin Panel')
@section('content')

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Services Management</h1>
        <p class="panel-page-sub">Manage hot tub service offerings</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddService">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Service
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

{{-- ─── ADD SERVICE FORM ─────────────────────────────────────────── --}}
<div class="card" id="addServiceCard" style="display:{{ $errors->any() ? 'block' : 'none' }}">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Service</div>

    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Service Name *</label>
                <input name="name" class="form-input @error('name') is-invalid @enderror"
                       required placeholder="e.g., Annual Maintenance Service"
                       value="{{ old('name') }}">
            </div>

            <div class="form-group">
                <label class="form-label">URL Slug</label>
                <input name="slug" class="form-input @error('slug') is-invalid @enderror"
                       placeholder="Auto-generated from name if left blank"
                       value="{{ old('slug') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Typical Starting Price</label>
                <input name="price" type="number" step="0.01" min="0"
                       class="form-input @error('price') is-invalid @enderror" placeholder="e.g., 150.00"
                       value="{{ old('price') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Service Image</label>
                <input type="file" name="image" class="form-input @error('image') is-invalid @enderror" accept="image/*"
                       onchange="previewImage(this)">
                <div class="text-sm text-muted">Click to upload a service image</div>
                <img id="imagePreview" src="" alt=""
                     style="display:none;margin-top:8px;width:80px;height:80px;object-fit:cover;border-radius:6px;border:1.5px solid var(--gray-200)">
            </div>

            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input @error('status') is-invalid @enderror" required>
                    <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top:1rem">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input @error('description') is-invalid @enderror" rows="6"
                      placeholder="Enter service description...">{{ old('description') }}</textarea>
        </div>

        {{-- ─── What's Included ─────────────────────────────────────── --}}
        <div class="form-group" style="margin-top:1.25rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                <label class="form-label" style="margin:0;font-size:1rem;font-weight:700;color:var(--gray-900)">
                    What's Included
                </label>
                <button type="button" onclick="addInclude()"
                    style="background:rgba(13,148,136,.1);color:var(--teal,#0d9488);border:none;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:600;cursor:pointer">
                    + Add Item
                </button>
            </div>
            <div id="includesList">
                @if(old('includes'))
                    @foreach(old('includes') as $item)
                        <div style="display:flex;gap:8px;margin-bottom:8px">
                            <input class="form-input" name="includes[]"
                                   placeholder="Included item #{{ $loop->iteration }}"
                                   value="{{ $item }}">
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                        </div>
                    @endforeach
                @else
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input class="form-input" name="includes[]" placeholder="Included item #1">
                        <button type="button" onclick="this.parentElement.remove()"
                            style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>
                    </div>
                @endif
            </div>
        </div>
        @include('components.upload-progress')

        <div class="modal-actions" style="justify-content:flex-start;margin-top:1rem">
            <button class="btn btn--primary" type="submit">Create Service</button>
            <button type="button" class="btn btn--ghost" onclick="closeForm()">Cancel</button>
        </div>
    </form>
</div>

{{-- ─── SERVICES TABLE ───────────────────────────────────────────── --}}
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Service Name</th>
                <th>Price</th>
                <th>Included Items</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
                <tr>
                    <td style="width:60px">
                        @if($it->image_url)
                            <img src="{{ $it->image_url }}" alt="{{ $it->name }}"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                        @else
                            <div style="width:48px;height:48px;border:1px dashed var(--gray-300);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--gray-400)">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-700 text-dark">{{ $it->name }}</div>
                        <div class="text-sm text-muted">{{ $it->slug }}</div>
                    </td>
                    <td>
                        @if(!is_null($it->price))
                            £{{ number_format($it->price, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if(is_array($it->includes) && count($it->includes))
                            {{ count($it->includes) }} item{{ count($it->includes) !== 1 ? 's' : '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($it->status === 'active')
                            <span class="badge badge--success">Active</span>
                        @else
                            <span class="badge badge--dark">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-row">
                            <a href="{{ route('admin.services.edit', $it) }}" class="icon-btn" title="Edit">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.services.destroy', $it) }}"
                                  onsubmit="return confirm('Delete this service?')">
                                @csrf @method('DELETE')
                                <button class="icon-btn" title="Delete">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path d="M3 6h18M8 6v14m8-14v14M5 6l1-2h12l1 2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-muted" style="text-align:center;padding:2rem">
                        No services found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>

<script>
    // ── Toggle form ──────────────────────────────────────────────────
    const serviceCard = document.getElementById('addServiceCard');

    document.getElementById('toggleAddService')?.addEventListener('click', function () {
        serviceCard.style.display = serviceCard.style.display === 'none' ? '' : 'none';
    });

    function closeForm() {
        serviceCard.style.display = 'none';
    }

    // ── What's Included dynamic rows ─────────────────────────────────
    let itemCt = document.querySelectorAll('#includesList input').length;

    function addInclude() {
        itemCt++;
        const row = document.createElement('div');
        row.style = 'display:flex;gap:8px;margin-bottom:8px';
        row.innerHTML = `<input class="form-input" name="includes[]" placeholder="Included item #${itemCt}">`
                      + `<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:18px;line-height:1;padding:0 6px">×</button>`;
        document.getElementById('includesList').appendChild(row);
    }

    // ── Image preview ────────────────────────────────────────────────
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@endsection
