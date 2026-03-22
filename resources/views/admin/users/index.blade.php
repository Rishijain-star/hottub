@extends('layouts.admin')
@section('title', 'User Management – Admin Panel')

@section('content')

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--danger">{{ session('error') }}</div>
@endif

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">User Management</h1>
        <p class="panel-page-sub">Manage customer accounts, pause or freeze access</p>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>User Info</th>
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
                        @if($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
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
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-5">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">
        {{ $users->links() }}
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
    });
</script>
@endsection
