@extends('layouts.admin')
@section('title', __('panel.admin.pages.users_index.title') . ' – Admin Panel')

@section('content')

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--danger">{{ session('error') }}</div>
@endif

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.users_index.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.users_index.sub') }}</p>
    </div>
    @if(auth()->user()?->isFullAdmin())
    <a href="{{ route('admin.users.create') }}" class="btn btn--primary btn--pill">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add sub-admin
    </a>
    @endif
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.users.index') }}" class="panel-filter-form panel-filter-form--4">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name or Email..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Role</label>
            <select name="role" class="form-input">
                <option value="">All roles</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Customer</option>
                <option value="sub_admin" {{ request('role') === 'sub_admin' ? 'selected' : '' }}>Sub-admin</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>Frozen</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>User Info</th>
                <th>Role</th>
                <th>Contact</th>
                <th>Postcode</th>
                <th>Joined Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        @if($user->profile_picture)
                            <img src="{{ \App\Support\PublicMedia::url($user->profile_picture) }}" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.75rem; font-weight: 800;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="fw-700 text-dark text-sm">{{ $user->name }}</div>
                            <div class="text-xs text-muted">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->role === \App\Models\User::ROLE_SUB_ADMIN)
                        <span class="text-xs fw-700" style="display:inline-block;padding:0.2rem 0.5rem;border-radius:999px;background:#e0e7ff;color:#3730a3;">Sub-admin</span>
                    @else
                        <span class="text-xs text-muted">Customer</span>
                    @endif
                </td>
                <td>
                    @if($user->phone)
                        <div class="text-sm">{{ $user->phone }}</div>
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
                </td>
                <td>
                    <span class="text-sm">{{ $user->postcode ?? '—' }}</span>
                </td>
                <td>
                    <div class="text-sm">{{ $user->created_at->format('M d, Y') }}</div>
                    <div class="text-xs text-muted">{{ $user->created_at->diffForHumans() }}</div>
                </td>
                <td>
                    <div class="actions-row">
                        @php $canEdit = $user->role !== \App\Models\User::ROLE_SUB_ADMIN || auth()->user()?->isFullAdmin(); @endphp
                        @if($canEdit)
                        <button type="button"
                                class="icon-btn js-open-edit"
                                title="Edit user"
                                data-action="{{ route('admin.users.update', $user) }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-phone="{{ $user->phone }}"
                                data-postcode="{{ $user->postcode }}"
                                data-status="{{ $user->status }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                        <button type="button"
                                class="icon-btn js-open-password"
                                title="Set new password"
                                data-action="{{ route('admin.users.reset-password', $user) }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                            </svg>
                        </button>
                        @if(auth()->user()?->isFullAdmin() && auth()->id() !== $user->id)
                        <button type="button"
                                class="icon-btn js-open-delete"
                                title="Delete"
                                data-action="{{ route('admin.users.destroy', $user) }}"
                                data-entity="user">✕</button>
                        @endif
                        @else
                        <span class="text-xs text-muted" title="Only the main admin can edit sub-admins">—</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap mt-4">
        {{ $users->links('components.pagination') }}
    </div>
</div>

{{-- ══ EDIT USER MODAL ══════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="editUserModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Edit User</div>
            <button type="button" class="modal-close" data-close="#editUserModal">✕</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
                <div class="grid grid--2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postcode</label>
                        <input type="text" name="postcode" id="edit_postcode" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select name="status" id="edit_status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="frozen">Frozen</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">Update User</button>
                <button type="button" class="btn btn--ghost" data-close="#editUserModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="passwordModal">
    <div class="modal-container modal-container--sm">
        <div class="modal-header">
            <div class="modal-title">Set New Password</div>
            <button type="button" class="modal-close" data-close="#passwordModal">✕</button>
        </div>
        <div class="text-sm text-muted" id="passwordTargetText">Update account password.</div>
        <form id="passwordForm" method="POST" action="#">
            @csrf
            <div class="form-group mt-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" minlength="8" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">Update Password</button>
                <button type="button" class="btn btn--ghost" data-close="#passwordModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@include('components.delete-confirm-modal')

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('editUserModal');
        const form  = document.getElementById('editUserForm');

        document.querySelectorAll('.js-open-edit').forEach(btn => {
            btn.addEventListener('click', function(){
                form.action = this.dataset.action;
                document.getElementById('edit_name').value     = this.dataset.name;
                document.getElementById('edit_email').value    = this.dataset.email;
                document.getElementById('edit_phone').value    = this.dataset.phone;
                document.getElementById('edit_postcode').value = this.dataset.postcode;
                
                // Map 'approved' or 'pending' to 'active' for the dropdown if needed
                let status = this.dataset.status;
                if (status === 'approved' || status === 'pending') status = 'active';
                document.getElementById('edit_status').value = status;

                modal.classList.add('active');
            });
        });

        document.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.dataset.close);
                if (target) target.classList.remove('active');
            });
        });

        document.querySelectorAll('.js-open-password').forEach(btn => {
            btn.addEventListener('click', function(){
                const passwordForm = document.getElementById('passwordForm');
                const targetText = document.getElementById('passwordTargetText');

                if (passwordForm) {
                    passwordForm.action = this.dataset.action;
                    passwordForm.reset();
                }
                if (targetText) {
                    targetText.textContent = `Set a new password for ${this.dataset.name || 'this account'} (${this.dataset.email || ''}).`;
                }
                const passwordModal = document.getElementById('passwordModal');
                if (passwordModal) passwordModal.classList.add('active');
            });
        });

    });
</script>
@endsection
