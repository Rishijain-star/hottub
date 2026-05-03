@extends('layouts.manufacturer')
@section('title', 'My Customers – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">My Customers</h1>
        <p class="panel-page-sub">Converted customers — open Messages to chat.</p>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <form method="GET" action="{{ route('manufacturer.customers.index') }}" class="panel-filter-form panel-filter-form--2">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Search by name, email, or phone" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
                <button type="submit" class="btn btn--primary">Search</button>
                <a href="{{ route('manufacturer.customers.index') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="overflow-x:auto;">
    @if($customers->isEmpty())
        <p class="text-muted" style="padding:1rem;">No converted customers yet. When you mark a lead as delivered / won, it will appear here.</p>
    @else
        <table class="table" style="width:100%;border-collapse:collapse;font-size:0.9rem;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid var(--gray-200);">
                    <th style="padding:0.65rem 0.5rem;">Customer</th>
                    <th style="padding:0.65rem 0.5rem;">Contact</th>
                    <th style="padding:0.65rem 0.5rem;">Plan overview</th>
                    <th style="padding:0.65rem 0.5rem;">Next follow-up</th>
                    <th style="padding:0.65rem 0.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $lead)
                    @php
                        $nextTask = ($openTasksByLead[$lead->id] ?? collect())->first();
                    @endphp
                    <tr style="border-bottom:1px solid var(--gray-100);vertical-align:top;">
                        <td style="padding:0.75rem 0.5rem;">
                            <div class="fw-700">{{ $lead->name }}</div>
                            <div class="text-xs text-muted">Updated {{ $lead->updated_at?->diffForHumans() }}</div>
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            <div>{{ $lead->email }}</div>
                            <div class="text-muted">{{ $lead->phone ?: '—' }}</div>
                            @php $chatUser = isset($customerUsersByEmail) ? $customerUsersByEmail->get(strtolower((string) $lead->email)) : null; @endphp
                            @if(!$chatUser)
                                <div class="text-xs" style="margin-top:0.35rem;color:#b45309;">Reminder: ask the customer to create an account with this same email.</div>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @php
                                $plan = $chatUser ? (($customerPlanByUserId ?? collect())->get($chatUser->id)) : null;
                            @endphp
                            @if($plan)
                                @php
                                    $statusLabel = match ($plan->status) {
                                        'active' => 'Active',
                                        'cancellation_scheduled' => 'Scheduled for Cancellation',
                                        'cancelled' => 'Cancelled by Customer',
                                        'expired' => 'Expired',
                                        default => ucfirst((string) $plan->status),
                                    };
                                    $statusClass = match ($plan->status) {
                                        'active' => 'badge--success',
                                        'cancellation_scheduled' => 'badge--warning',
                                        'cancelled' => 'badge--danger',
                                        'expired' => '',
                                        default => '',
                                    };
                                    $typeLabel = (($plan->package->plan_type ?? 'yearly') === 'monthly') ? 'Monthly' : 'Yearly';
                                @endphp
                                <div class="fw-700">{{ $plan->package->name ?? 'Maintenance Plan' }}</div>
                                <div class="text-xs text-muted">Type: {{ $typeLabel }}</div>
                                <div class="text-xs text-muted">Amount: {{ isset($plan->package->price) ? number_format((float) $plan->package->price, 2) : '—' }}</div>
                                <div class="text-xs text-muted">Purchase: {{ optional($plan->created_at)->format('d M Y') ?: '—' }}</div>
                                <div class="text-xs text-muted">Expiry: {{ optional($plan->expiry_date)->format('d M Y') ?: '—' }}</div>
                                <div style="margin-top:0.35rem;">
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                @if($plan->status === 'cancellation_scheduled')
                                    <div class="text-xs" style="margin-top:0.3rem;color:#b45309;">
                                        Will cancel on {{ optional($plan->cancellation_effective_at ?? $plan->expiry_date)->format('d M Y') ?: '—' }}
                                    </div>
                                    @if(!empty($plan->cancellation_reason))
                                        <button type="button"
                                                class="btn btn--ghost btn--xs js-view-cancel-reason"
                                                data-customer="{{ $lead->name }}"
                                                data-reason="{{ $plan->cancellation_reason }}"
                                                style="margin-top:0.35rem;">
                                            View reason
                                        </button>
                                    @endif
                                @elseif($plan->status === 'cancelled')
                                    <div class="text-xs text-muted" style="margin-top:0.3rem;">
                                        Cancelled on {{ optional($plan->cancelled_at ?? $plan->cancellation_effective_at)->format('d M Y') ?: '—' }}
                                    </div>
                                    @if(!empty($plan->cancellation_reason))
                                        <button type="button"
                                                class="btn btn--ghost btn--xs js-view-cancel-reason"
                                                data-customer="{{ $lead->name }}"
                                                data-reason="{{ $plan->cancellation_reason }}"
                                                style="margin-top:0.35rem;">
                                            View reason
                                        </button>
                                    @endif
                                @endif
                            @else
                                <span class="text-muted text-sm">No maintenance plan</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @if($nextTask)
                                <span class="text-sm">{{ $nextTask->due_date->format('M j, Y') }}</span>
                                <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($nextTask->content, 60) }}</div>
                            @else
                                <span class="text-muted text-sm">None scheduled</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @if($chatUser)
                                <a href="{{ route('manufacturer.messages', ['with' => $chatUser->id]) }}" class="btn btn--primary btn--sm">Chat</a>
                            @else
                                <span class="text-muted text-sm">Waiting for account</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:1rem;">{{ $customers->links() }}</div>
    @endif
</div>

<div class="modal-backdrop" id="cancelReasonModalMfr" role="dialog" aria-modal="true" aria-labelledby="cancelReasonModalMfrTitle">
    <div class="modal" style="width:min(520px,94vw)" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title" id="cancelReasonModalMfrTitle">Cancellation Reason</div>
            <button type="button" class="modal-close" id="cancelReasonModalMfrClose" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="text-sm text-muted" id="cancelReasonModalMfrCustomer" style="margin-bottom:0.4rem;"></div>
            <div class="card" style="margin:0;padding:0.9rem;">
                <div id="cancelReasonModalMfrText" class="text-sm"></div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    (function () {
        function openCancelReasonMfr(customer, reason) {
            document.getElementById('cancelReasonModalMfrCustomer').textContent = customer ? ('Customer: ' + customer) : '';
            document.getElementById('cancelReasonModalMfrText').textContent = reason || 'No reason provided.';
            document.getElementById('cancelReasonModalMfr').classList.add('active');
        }
        function closeCancelReasonMfr() {
            document.getElementById('cancelReasonModalMfr').classList.remove('active');
        }
        var modalEl = document.getElementById('cancelReasonModalMfr');
        var closeBtnEl = document.getElementById('cancelReasonModalMfrClose');
        if (closeBtnEl) {
            closeBtnEl.addEventListener('click', function () {
                closeCancelReasonMfr();
            });
        }
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                if (e.target === modalEl) {
                    closeCancelReasonMfr();
                }
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCancelReasonMfr();
            }
        });
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.js-view-cancel-reason');
            if (trigger) {
                openCancelReasonMfr(trigger.getAttribute('data-customer'), trigger.getAttribute('data-reason'));
                return;
            }
        });
    })();
</script>
@endsection
@endsection
