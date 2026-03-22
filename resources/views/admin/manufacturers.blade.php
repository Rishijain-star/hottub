@extends('layouts.admin')
@section('title', 'Manufacturers – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Manufacturer Management</h1><p class="panel-page-sub">Approve manufacturers, manage credits, and edit profile information</p></div>
    <button class="btn btn--primary btn--pill" id="toggleCreateManu">Create Manufacturer</button>
    </div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card" id="createManuCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Create New Manufacturer</div>
    <form method="POST" action="{{ route('admin.manufacturers.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Contact Name *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" class="form-input" type="email" required></div>
            <div class="form-group"><label class="form-label">Company Name *</label><input name="company_name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">VAT Number</label><input name="vat_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">Company Number</label><input name="company_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">Postcode</label><input name="postcode" class="form-input"></div>
            <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-input"></div>
            <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input"></div>
            <div class="form-group"><label class="form-label">Temporary Password *</label><input name="password" class="form-input" type="password" required></div>
        </div>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">Create</button>
        </div>
    </form>
    <script>
        document.getElementById('toggleCreateManu')?.addEventListener('click', function(){
            const el = document.getElementById('createManuCard');
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });
    </script>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th></th><th>Manufacturer Info</th><th>Company</th><th>Contact</th><th>Credits</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($manufacturers as $m)
            <tr>
                <td style="width: 70px; text-align: center; vertical-align: middle; padding-left: 1.5rem;">
                    <div style="width: 50px; height: 50px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;">
                        @if($m->profile_picture)
                            <img src="{{ asset('storage/' . $m->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 loading="lazy"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <div class="letter-avatar" style="width: 100%; height: 100%; font-size: 1.5rem;">
                                {{ substr($m->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $m->name }}</div>
                    <div class="text-sm text-muted">{{ $m->email }}</div>
                </td>
                <td>
                    <div>{{ $m->company_name ?? '—' }}</div>
                    <div class="text-sm text-muted">Co: {{ $m->company_number ?? '—' }}</div>
                </td>
                <td>
                    <div>📞 {{ $m->phone ?? '—' }}</div>
                    <div class="text-sm text-muted">{{ $m->postcode ?? '—' }}</div>
                </td>
                <td>{{ $m->credits ?? 0 }} <a href="{{ route('admin.manufacturers.credits', $m) }}" class="btn btn--ghost btn--sm">+</a></td>
                <td>
                    @if($m->status==='approved')
                        <span class="badge badge--success">Approved</span>
                    @elseif($m->status==='pending')
                        <span class="badge">Pending</span>
                    @elseif($m->status==='paused')
                        <span class="badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fdba74">Paused</span>
                    @elseif($m->status==='frozen')
                        <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca">Frozen</span>
                    @else
                        <span class="badge badge--dark">Revoked</span>
                    @endif
                </td>
                <td>
                    <div class="actions-row">
                        @if($m->status!=='approved')
                            <form method="POST" action="{{ route('admin.manufacturers.approve', $m) }}">@csrf @method('PATCH') <button class="btn btn--ghost btn--sm">Approve</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.manufacturers.revoke', $m) }}">@csrf @method('PATCH') <button class="btn btn--danger btn--sm">Revoke</button></form>
                        @endif
                        <button type="button"
                           class="icon-btn js-open-edit"
                           title="Edit manufacturer"
                           data-action="{{ route('admin.manufacturers.update', $m) }}"
                           data-name="{{ $m->name }}"
                           data-email="{{ $m->email }}"
                           data-company_name="{{ $m->company_name }}"
                           data-company_number="{{ $m->company_number }}"
                           data-vat_number="{{ $m->vat_number }}"
                           data-phone="{{ $m->phone }}"
                           data-postcode="{{ $m->postcode }}"
                           data-address="{{ $m->address }}"
                           data-website="{{ $m->website }}"
                           data-status="{{ $m->status }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('admin.manufacturers.destroy', $m) }}" onsubmit="return confirm('Delete this manufacturer?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn" title="Delete">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">No manufacturers found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $manufacturers->links('components.pagination') }}</div>
</div>

<div class="modal-backdrop" id="editManufacturerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Edit Manufacturer - Account Control</div>
            <button type="button" class="modal-close" data-close="#editManufacturerModal">✕</button>
        </div>
        <form id="editManufacturerForm" method="POST" action="#">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="grid grid--2">
                    <div class="form-group"><label class="form-label">Contact Name *</label><input name="name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Company Name *</label><input name="company_name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Company Number</label><input name="company_number" class="form-input"></div>
                    <div class="form-group"><label class="form-label">VAT Number</label><input name="vat_number" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Postcode</label><input name="postcode" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input"></div>
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <select name="status" class="form-input" id="editManufacturerStatus">
                            <option value="pending">Pending Approval</option>
                            <option value="approved">Approved / Active</option>
                            <option value="paused">Pause Account</option>
                            <option value="frozen">Freeze Account</option>
                            <option value="revoked">Revoked / Disabled</option>
                        </select>
                    </div>
                    <div class="form-group" id="resumeOptionContainer" style="display:none; grid-column: span 2;">
                        <div class="alert alert--warning" style="margin-bottom:0; display:flex; align-items:center; justify-content:space-between;">
                            <span>This account is currently <strong>paused or frozen</strong>.</span>
                            <button type="button" class="btn btn--sm btn--primary" onclick="resumeAccount()">Resume Account</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <button type="button" class="btn" data-close="#editManufacturerModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function openModal(id) {
        const modal = document.querySelector(id);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.querySelector(id);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    window.resumeAccount = function() {
        const statusSel = document.getElementById('editManufacturerStatus');
        const resumeCont = document.getElementById('resumeOptionContainer');
        if (statusSel) {
            statusSel.value = 'approved';
            if (resumeCont) resumeCont.style.display = 'none';
        }
    };

    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(btn.getAttribute('data-close'));
        });
    });

    const editForm = document.getElementById('editManufacturerForm');
    const statusSel = document.getElementById('editManufacturerStatus');
    const resumeCont = document.getElementById('resumeOptionContainer');

    document.querySelectorAll('.js-open-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (editForm) {
                editForm.action = btn.getAttribute('data-action');
                const fields = ['name', 'email', 'company_name', 'company_number', 'vat_number', 'phone', 'postcode', 'address', 'website', 'status'];
                
                fields.forEach(function(key) {
                    const input = editForm.querySelector('[name="' + key + '"]');
                    if (input) {
                        const val = btn.getAttribute('data-' + key) || '';
                        input.value = val;
                        
                        if (key === 'status') {
                            if (val === 'paused' || val === 'frozen') {
                                if (resumeCont) resumeCont.style.display = 'block';
                            } else {
                                if (resumeCont) resumeCont.style.display = 'none';
                            }
                        }
                    }
                });
            }
            openModal('#editManufacturerModal');
        });
    });

    if (statusSel) {
        statusSel.addEventListener('change', function() {
            if (this.value === 'paused' || this.value === 'frozen') {
                if (resumeCont) resumeCont.style.display = 'block';
            } else {
                if (resumeCont) resumeCont.style.display = 'none';
            }
        });
    }
});
</script>
@endsection
@endsection
