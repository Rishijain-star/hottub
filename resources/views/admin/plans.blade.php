@extends('layouts.admin')
@section('title', 'Plans – Admin Panel')
@section('content')

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Credit Plans</h1>
        <p class="panel-page-sub">Manage subscription and credit top-up plans</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddPlan">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Plan
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

{{-- ─── ADD PLAN FORM ─────────────────────────────────────────── --}}
<div class="card mb-4" id="addPlanCard" style="display:{{ $errors->any() ? 'block' : 'none' }}">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Add New Credit Plan</div>

    <form method="POST" action="{{ route('admin.plans.store') }}">
        @csrf
        <div class="grid grid--3">
            <div class="form-group">
                <label class="form-label">Plan Name *</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g., Basic Top-up" value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Credits Included *</label>
                <input type="number" name="credits" class="form-input" required placeholder="e.g., 50" value="{{ old('credits') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Price (£) *</label>
                <input type="number" step="0.01" name="price" class="form-input" required placeholder="e.g., 99.00" value="{{ old('price') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Validity (Days) *</label>
                <input type="number" name="validity_days" class="form-input" required placeholder="e.g., 30" value="{{ old('validity_days') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Badge Type</label>
                <input type="text" name="badge_type" class="form-input" placeholder="e.g., Verified Badge" value="{{ old('badge_type') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="2" placeholder="Plan details...">{{ old('description') }}</textarea>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button type="submit" class="btn btn--primary">Save Plan</button>
            <button type="button" class="btn btn--ghost" id="cancelAddPlan">Cancel</button>
        </div>
    </form>
</div>

{{-- ─── PLANS TABLE ─────────────────────────────────────────────── --}}
<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>PLAN NAME</th>
                <th>CREDITS</th>
                <th>PRICE</th>
                <th>VALIDITY</th>
                <th>BADGE</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
                <tr>
                    <td>
                        <div class="fw-700 text-dark">{{ $p->name }}</div>
                        <div class="text-xs text-muted">{{ $p->description }}</div>
                    </td>
                    <td><div class="fw-700">{{ $p->credits }}</div></td>
                    <td><div class="fw-700 text-teal">£{{ number_format($p->price, 2) }}</div></td>
                    <td>{{ $p->validity_days }} Days</td>
                    <td>{{ $p->badge_type ?: '—' }}</td>
                    <td>
                        @if($p->is_active)
                            <span class="badge badge--success">Active</span>
                        @else
                            <span class="badge badge--danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-row">
                            <button class="btn btn--ghost--panel btn--sm" onclick="editPlan({{ json_encode($p) }})">Edit</button>
                            <form action="{{ route('admin.plans.destroy', $p) }}" method="POST" onsubmit="return confirm('Delete this plan?')">
                                @csrf @method('DELETE')
                                <button class="btn btn--danger-soft btn--sm" style="color:red">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center p-4 text-muted">No plans created yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ─── EDIT PLAN MODAL ────────────────────────────────────────── --}}
<div class="modal-backdrop" id="editPlanModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Edit Credit Plan</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editPlanForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="grid grid--2">
                    <div class="form-group">
                        <label class="form-label">Plan Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Credits *</label>
                        <input type="number" name="credits" id="edit_credits" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (£) *</label>
                        <input type="number" step="0.01" name="price" id="edit_price" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Validity (Days) *</label>
                        <input type="number" name="validity_days" id="edit_validity" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Badge Type</label>
                        <input type="text" name="badge_type" id="edit_badge" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="edit_status" class="form-input">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_desc" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn--ghost" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn--primary">Update Plan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('toggleAddPlan').addEventListener('click', function() {
        document.getElementById('addPlanCard').style.display = 'block';
    });
    document.getElementById('cancelAddPlan').addEventListener('click', function() {
        document.getElementById('addPlanCard').style.display = 'none';
    });

    function editPlan(plan) {
        document.getElementById('editPlanForm').action = `/admin/plans/${plan.id}`;
        document.getElementById('edit_name').value = plan.name;
        document.getElementById('edit_credits').value = plan.credits;
        document.getElementById('edit_price').value = plan.price;
        document.getElementById('edit_validity').value = plan.validity_days;
        document.getElementById('edit_badge').value = plan.badge_type || '';
        document.getElementById('edit_desc').value = plan.description || '';
        document.getElementById('edit_status').value = plan.is_active ? '1' : '0';
        document.getElementById('editPlanModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editPlanModal').classList.remove('active');
    }
</script>
@endsection
