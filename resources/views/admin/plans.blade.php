@extends('layouts.admin')
@section('title', __('panel.admin.nav.credit_plans') . ' - ' . __('panel.admin_title'))
@section('content')

<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.plans.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.plans.sub') }}</p>
    </div>
    <button class="btn btn--primary btn--pill" id="toggleAddPlan">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ __('panel.admin.plans.add_plan') }}
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
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.plans.add_new') }}</div>

    <form method="POST" action="{{ route('admin.plans.store') }}">
        @csrf
        <div class="grid grid--3">
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.plans.plan_name') }} *</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g., Basic Top-up" value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.plans.credits_included') }} *</label>
                <input type="number" name="credits" class="form-input" required placeholder="e.g., 50" value="{{ old('credits') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.common.price') }} (£) *</label>
                <input type="number" step="0.01" name="price" class="form-input" required placeholder="e.g., 99.00" value="{{ old('price') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.plans.validity_days') }} *</label>
                <input type="number" name="validity_days" class="form-input" required placeholder="e.g., 30" value="{{ old('validity_days') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.admin.plans.badge_type') }}</label>
                <input type="text" name="badge_type" class="form-input" placeholder="e.g., Verified Badge" value="{{ old('badge_type') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('panel.admin.common.description') }}</label>
            <textarea name="description" class="form-input" rows="2" placeholder="Plan details...">{{ old('description') }}</textarea>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button type="submit" class="btn btn--primary">{{ __('panel.admin.plans.save_plan') }}</button>
            <button type="button" class="btn btn--ghost" id="cancelAddPlan">{{ __('panel.admin.common.cancel') }}</button>
        </div>
    </form>
</div>

{{-- ─── PLANS TABLE ─────────────────────────────────────────────── --}}
<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>{{ strtoupper(__('panel.admin.plans.plan_name')) }}</th>
                <th>{{ strtoupper(__('panel.admin.plans.credits_included')) }}</th>
                <th>{{ strtoupper(__('panel.admin.common.price')) }}</th>
                <th>{{ strtoupper(__('panel.admin.plans.validity_days')) }}</th>
                <th>{{ strtoupper(__('panel.admin.plans.badge_type')) }}</th>
                <th>{{ strtoupper(__('panel.admin.common.status')) }}</th>
                <th>{{ strtoupper(__('panel.admin.common.actions')) }}</th>
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
                    <td>{{ $p->validity_days }} {{ __('panel.admin.plans.validity_days') }}</td>
                    <td>{{ $p->badge_type ?: '—' }}</td>
                    <td>
                        @if($p->is_active)
                            <span class="badge badge--success">{{ __('panel.admin.plans.active') }}</span>
                        @else
                            <span class="badge badge--danger">{{ __('panel.admin.plans.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-row">
                            <button class="btn btn--ghost--panel btn--sm" onclick="editPlan({{ json_encode($p) }})">{{ __('panel.admin.common.edit') }}</button>
                            <form action="{{ route('admin.plans.destroy', $p) }}" method="POST" onsubmit="return confirm('{{ __('panel.admin.plans.delete_plan_confirm') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn--danger-soft btn--sm" style="color:red">{{ __('panel.admin.common.delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center p-4 text-muted">{{ __('panel.admin.plans.no_plans') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ─── EDIT PLAN MODAL ────────────────────────────────────────── --}}
<div class="modal-backdrop" id="editPlanModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">{{ __('panel.admin.plans.edit_plan') }}</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editPlanForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="grid grid--2">
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.plans.plan_name') }} *</label>
                        <input type="text" name="name" id="edit_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.plans.credits_included') }} *</label>
                        <input type="number" name="credits" id="edit_credits" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.common.price') }} (£) *</label>
                        <input type="number" step="0.01" name="price" id="edit_price" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.plans.validity_days') }} *</label>
                        <input type="number" name="validity_days" id="edit_validity" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.plans.badge_type') }}</label>
                        <input type="text" name="badge_type" id="edit_badge" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('panel.admin.common.status') }}</label>
                        <select name="is_active" id="edit_status" class="form-input">
                            <option value="1">{{ __('panel.admin.plans.active') }}</option>
                            <option value="0">{{ __('panel.admin.plans.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.common.description') }}</label>
                    <textarea name="description" id="edit_desc" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn--ghost" onclick="closeEditModal()">{{ __('panel.admin.common.cancel') }}</button>
                <button type="submit" class="btn btn--primary">{{ __('panel.admin.plans.update_plan') }}</button>
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
