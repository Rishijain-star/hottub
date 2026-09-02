@extends('layouts.manufacturer')
@section('title', __('panel.package_requests.title').' - '.__('panel.manufacturer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.package_requests.title') }}</h1><p class="panel-page-sub">{{ __('panel.package_requests.sub') }}</p></div>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('manufacturer.package-requests') }}" class="panel-filter-form panel-filter-form--3">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.common.search_customer') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.status') }}</label>
            <select name="status" class="form-input">
                <option value="">{{ __('panel.common.all_status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('panel.status.pending') }}</option>
                <option value="responded" {{ request('status') === 'responded' ? 'selected' : '' }}>{{ __('panel.package_requests.responded') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('panel.package_requests.active') }}</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('panel.package_requests.expired') }}</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.common.filter') }}</button>
            <a href="{{ route('manufacturer.package-requests') }}" class="btn btn--ghost">{{ __('panel.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.package_requests.customer') }}</th>
                <th>{{ __('panel.package_requests.package_requested') }}</th>
                <th>{{ __('panel.package_requests.price') }}</th>
                <th>{{ __('panel.package_requests.status') }}</th>
                <th>{{ __('panel.package_requests.date') }}</th>
                <th>{{ __('panel.package_requests.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->customer->name }}</div>
                    <div class="text-sm text-muted">{{ $req->customer->email }}</div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $req->package->name }}</div>
                </td>
                <td class="fw-700 text-primary">£{{ number_format($req->package->price, 2) }}</td>
                <td>
                    @if($req->status === 'pending')
                        <span class="badge badge--warning">{{ __('panel.package_requests.urgent_follow_up') }}</span>
                    @elseif($req->status === 'active')
                        <span class="badge badge--success">{{ __('panel.package_requests.active') }}</span>
                    @elseif($req->status === 'responded')
                        <span class="badge badge--success">{{ __('panel.package_requests.responded') }}</span>
                    @elseif($req->status === 'expired')
                        <span class="badge badge--danger">{{ __('panel.package_requests.expired') }}</span>
                    @else
                        <span class="badge badge--dark">{{ __('panel.package_requests.closed') }}</span>
                    @endif
                </td>
                <td>{{ $req->created_at->format('d M Y') }}</td>
                <td>
                    <div class="actions-row">
                        <button class="btn btn--ghost btn--sm" onclick="viewRequestDetails({{ json_encode($req) }})">{{ __('panel.package_requests.view') }}</button>
                        
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('manufacturer.package-requests.update', $req) }}" style="display:inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="active">
                            <button class="btn btn--primary btn--xs">{{ __('panel.package_requests.activate_plan') }}</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.package_requests.no_requests') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($requests->hasPages())
        <div style="padding:1rem">{{ $requests->links('components.pagination') }}</div>
    @endif
</div>

{{-- Detail Modal --}}
<div id="requestModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="document.getElementById('requestModal').style.display='none'">&times;</button>
        <h3 id="modalTitle" style="margin-top:0; font-weight: 800;">{{ __('panel.package_requests.request_details') }}</h3>
        <div id="modalBody" style="margin-top:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('requestModal').style.display='none'">{{ __('panel.common.close') }}</button>
        </div>
    </div>
</div>

<script>
function viewRequestDetails(req) {
    document.getElementById('modalTitle').textContent = @json(__('panel.package_requests.package_request', ['name' => '___NAME___'])).replace('___NAME___', req.package.name);
    document.getElementById('modalBody').innerHTML = `
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.customer_label') }}</span> ${req.customer.name}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.email_label') }}</span> ${req.customer.email}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.package_label') }}</span> ${req.package.name} (£${req.package.price})</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.plan_type_label') }}</span> ${(req.package.plan_type || 'yearly').toUpperCase()}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.start_date_label') }}</span> ${req.start_date ? new Date(req.start_date).toLocaleDateString() : '—'}</div>
        <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.expiry_date_label') }}</span> ${req.expiry_date ? new Date(req.expiry_date).toLocaleDateString() : '—'}</div>
        <div style="margin-bottom:15px;"><span class="fw-700 text-dark">{{ __('panel.package_requests.message_from_customer') }}</span><br><p class="text-sm text-muted" style="margin-top: 5px;">${req.message || '{{ __('panel.service_requests.no_message') }}'}</p></div>
    `;
    document.getElementById('requestModal').style.display = 'flex';
}
</script>
@endsection
