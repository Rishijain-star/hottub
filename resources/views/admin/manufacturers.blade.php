@extends('layouts.admin')
@section('title', __('panel.admin.nav.manufacturers') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.manufacturers.title') }}</h1><p class="panel-page-sub">{{ __('panel.admin.manufacturers.sub') }}</p></div>
    <button class="btn btn--primary btn--pill" id="toggleCreateManu">{{ __('panel.admin.manufacturers.create') }}</button>
    </div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.manufacturers') }}" class="panel-filter-form panel-filter-form--3">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.admin.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="Name, Email, or Company..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.admin.common.status') }}</label>
            <select name="status" class="form-input">
                <option value="">{{ __('panel.admin.common.all_status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('panel.admin.dealers.pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('panel.admin.dealers.approved') }}</option>
                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>{{ __('panel.admin.dealers.revoked') }}</option>
                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>{{ __('panel.admin.dealers.paused') }}</option>
                <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>{{ __('panel.admin.dealers.frozen') }}</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.admin.common.filter') }}</button>
            <a href="{{ route('admin.manufacturers') }}" class="btn btn--ghost">{{ __('panel.admin.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card" id="createManuCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.manufacturers.create_new') }}</div>
    <form method="POST" action="{{ route('admin.manufacturers.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.contact_name') }} *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.email') }} *</label><input name="email" class="form-input" type="email" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.company_name') }} *</label><input name="company_name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.vat_number') }}</label><input name="vat_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.company_number') }}</label><input name="company_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.phone') }}</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.postcode') }}</label><input name="postcode" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.address') }}</label><input name="address" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.website') }}</label><input name="website" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.temporary_password') }} *</label><input name="password" class="form-input" type="password" required></div>
        </div>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">{{ __('panel.admin.common.create') }}</button>
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
        <thead><tr><th></th><th>{{ __('panel.admin.manufacturers.manufacturer_info') }}</th><th>{{ __('panel.admin.common.company') }}</th><th>{{ __('panel.admin.manufacturers.contact') }}</th><th>{{ __('panel.admin.dealers.credits') }}</th><th>{{ __('panel.admin.common.status') }}</th><th>{{ __('panel.admin.common.actions') }}</th></tr></thead>
        <tbody>
        @forelse($manufacturers as $m)
            <tr>
                <td style="width: 70px; text-align: center; vertical-align: middle; padding-left: 1.5rem;">
                    <div style="width: 50px; height: 50px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;">
                        @if($m->profile_picture)
                            <img src="{{ \App\Support\PublicMedia::url($m->profile_picture) }}" 
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
                        <span class="badge badge--success">{{ __('panel.admin.dealers.approved') }}</span>
                    @elseif($m->status==='pending')
                        <span class="badge">{{ __('panel.admin.dealers.pending') }}</span>
                    @elseif($m->status==='paused')
                        <span class="badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fdba74">{{ __('panel.admin.dealers.paused') }}</span>
                    @elseif($m->status==='frozen')
                        <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca">{{ __('panel.admin.dealers.frozen') }}</span>
                    @else
                        <span class="badge badge--dark">{{ __('panel.admin.dealers.revoked') }}</span>
                    @endif
                </td>
                <td>
                    <div class="actions-row">
                        @if($m->status!=='approved')
                            <form method="POST" action="{{ route('admin.manufacturers.approve', $m) }}">@csrf @method('PATCH') <button class="btn btn--ghost btn--sm">{{ __('panel.admin.common.approve') }}</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.manufacturers.revoke', $m) }}">@csrf @method('PATCH') <button class="btn btn--danger btn--sm">{{ __('panel.admin.dealers.revoke') }}</button></form>
                        @endif
                        <button type="button"
                           class="icon-btn js-open-edit"
                           title="{{ __('panel.admin.common.edit') }}"
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
                        <button type="button"
                           class="icon-btn js-open-password"
                           title="{{ __('panel.admin.dealers.new_password') }}"
                           data-name="{{ $m->name }}"
                           data-email="{{ $m->email }}"
                           data-action="{{ route('admin.manufacturers.reset-password', $m) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                            </svg>
                        </button>
                        <button type="button"
                           class="icon-btn js-open-delete"
                           title="{{ __('panel.admin.common.delete') }}"
                           data-action="{{ route('admin.manufacturers.destroy', $m) }}"
                           data-entity="manufacturer">✕</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">{{ __('panel.admin.manufacturers.no_manufacturers_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $manufacturers->links('components.pagination') }}</div>
</div>

<div class="modal-backdrop" id="editManufacturerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">{{ __('panel.admin.manufacturers.edit_manufacturer') }}</div>
            <button type="button" class="modal-close" data-close="#editManufacturerModal">✕</button>
        </div>
        <form id="editManufacturerForm" method="POST" action="#">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="grid grid--2">
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.contact_name') }} *</label><input name="name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.common.email') }} *</label><input type="email" name="email" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.company_name') }} *</label><input name="company_name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.company_number') }}</label><input name="company_number" class="form-input"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.vat_number') }}</label><input name="vat_number" class="form-input"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.common.phone') }}</label><input name="phone" class="form-input"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.common.postcode') }}</label><input name="postcode" class="form-input"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.address') }}</label><input name="address" class="form-input"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.dealers.website') }}</label><input name="website" class="form-input"></div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.dealers.account_status') }}</label>
                        <select name="status" class="form-input" id="editManufacturerStatus">
                            <option value="pending">{{ __('panel.admin.dealers.pending_approval') }}</option>
                            <option value="approved">{{ __('panel.admin.dealers.approved_active') }}</option>
                            <option value="paused">{{ __('panel.admin.dealers.pause_account') }}</option>
                            <option value="frozen">{{ __('panel.admin.dealers.freeze_account') }}</option>
                            <option value="revoked">{{ __('panel.admin.dealers.revoked_disabled') }}</option>
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
                <button type="submit" class="btn btn--primary">{{ __('panel.admin.dealers.save_changes') }}</button>
                <button type="button" class="btn" data-close="#editManufacturerModal">{{ __('panel.admin.common.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="passwordModal">
    <div class="modal-container modal-container--sm">
        <div class="modal-header">
            <div class="modal-title">{{ __('panel.admin.dealers.new_password') }}</div>
            <button type="button" class="modal-close" data-close="#passwordModal">✕</button>
        </div>
        <div class="text-sm text-muted" id="passwordTargetText">Update account password.</div>
        <form id="passwordForm" method="POST" action="#">
            @csrf
            <div class="form-group mt-3">
                <label class="form-label">{{ __('panel.admin.dealers.new_password') }}</label>
                <input type="password" name="password" class="form-input" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.dealers.confirm_password') }}</label>
                <input type="password" name="password_confirmation" class="form-input" minlength="8" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">{{ __('panel.admin.dealers.update_password') }}</button>
                <button type="button" class="btn" data-close="#passwordModal">{{ __('panel.admin.common.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@include('components.delete-confirm-modal')

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

    document.querySelectorAll('.js-open-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = document.getElementById('passwordForm');
            const targetText = document.getElementById('passwordTargetText');
            if (form) {
                form.action = btn.getAttribute('data-action');
                form.reset();
            }
            if (targetText) {
                targetText.textContent = 'Set a new password for ' + (btn.getAttribute('data-name') || 'this account') + ' (' + (btn.getAttribute('data-email') || '') + ').';
            }
            openModal('#passwordModal');
        });
    });

});
</script>
@endsection
@endsection
