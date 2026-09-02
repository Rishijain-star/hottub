@extends('layouts.dealer')
@section('title', __('panel.customers.title').' - '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.customers.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.customers.sub') }}</p>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <form method="GET" action="{{ route('dealer.customers.index') }}" class="panel-filter-form panel-filter-form--2">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.customers.search_placeholder') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
                <button type="submit" class="btn btn--primary">{{ __('panel.common.search') }}</button>
                <a href="{{ route('dealer.customers.index') }}" class="btn btn--ghost">{{ __('panel.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="overflow-x:auto;">
    @if($customers->isEmpty())
        <p class="text-muted" style="padding:1rem;">{{ __('panel.customers.no_customers') }}</p>
    @else
        <table class="table" style="width:100%;border-collapse:collapse;font-size:0.9rem;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid var(--gray-200);">
                    <th style="padding:0.65rem 0.5rem;">{{ __('panel.customers.customer') }}</th>
                    <th style="padding:0.65rem 0.5rem;">{{ __('panel.customers.contact') }}</th>
                    <th style="padding:0.65rem 0.5rem;">{{ __('panel.customers.plan_overview') }}</th>
                    <th style="padding:0.65rem 0.5rem;">{{ __('panel.customers.next_follow_up') }}</th>
                    <th style="padding:0.65rem 0.5rem;">{{ __('panel.customers.actions') }}</th>
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
                            <div class="text-xs text-muted">{{ __('panel.customers.updated', ['time' => $lead->updated_at?->diffForHumans()]) }}</div>
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            <div>{{ $lead->email }}</div>
                            <div class="text-muted">{{ $lead->phone ?: '—' }}</div>
                            @php $chatUser = isset($customerUsersByEmail) ? $customerUsersByEmail->get(strtolower((string) $lead->email)) : null; @endphp
                            @if(!$chatUser)
                                <div class="text-xs" style="margin-top:0.35rem;color:#b45309;">{{ __('panel.customers.account_reminder') }}</div>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @php
                                $plan = $chatUser ? (($customerPlanByUserId ?? collect())->get($chatUser->id)) : null;
                            @endphp
                            @if($plan)
                                @php
                                    $statusLabel = match ($plan->status) {
                                        'active' => __('panel.customers.plan_active'),
                                        'cancellation_scheduled' => __('panel.customers.plan_cancel_scheduled'),
                                        'cancelled' => __('panel.customers.plan_cancelled'),
                                        'expired' => __('panel.customers.plan_expired'),
                                        default => ucfirst((string) $plan->status),
                                    };
                                    $statusClass = match ($plan->status) {
                                        'active' => 'badge--success',
                                        'cancellation_scheduled' => 'badge--warning',
                                        'cancelled' => 'badge--danger',
                                        'expired' => '',
                                        default => '',
                                    };
                                    $typeLabel = (($plan->package->plan_type ?? 'yearly') === 'monthly') ? __('panel.customers.monthly') : __('panel.customers.yearly');
                                @endphp
                                <div class="fw-700">{{ $plan->package->name ?? __('panel.customers.maintenance_plan') }}</div>
                                <div class="text-xs text-muted">{{ __('panel.customers.type', ['value' => $typeLabel]) }}</div>
                                <div class="text-xs text-muted">{{ __('panel.customers.amount', ['value' => isset($plan->package->price) ? number_format((float) $plan->package->price, 2) : '—']) }}</div>
                                <div class="text-xs text-muted">{{ __('panel.customers.purchase', ['date' => optional($plan->created_at)->format('d M Y') ?: '—']) }}</div>
                                <div class="text-xs text-muted">{{ __('panel.customers.expiry', ['date' => optional($plan->expiry_date)->format('d M Y') ?: '—']) }}</div>
                                <div style="margin-top:0.35rem;">
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                @if($plan->status === 'cancellation_scheduled')
                                    <div class="text-xs" style="margin-top:0.3rem;color:#b45309;">
                                        {{ __('panel.customers.cancel_on', ['date' => optional($plan->cancellation_effective_at ?? $plan->expiry_date)->format('d M Y') ?: '—']) }}
                                    </div>
                                    @if(!empty($plan->cancellation_reason))
                                        <button type="button"
                                                class="btn btn--ghost btn--xs js-view-cancel-reason"
                                                data-customer="{{ $lead->name }}"
                                                data-reason="{{ $plan->cancellation_reason }}"
                                                style="margin-top:0.35rem;">
                                            {{ __('panel.customers.view_reason') }}
                                        </button>
                                    @endif
                                @elseif($plan->status === 'cancelled')
                                    <div class="text-xs text-muted" style="margin-top:0.3rem;">
                                        {{ __('panel.customers.cancelled_on', ['date' => optional($plan->cancelled_at ?? $plan->cancellation_effective_at)->format('d M Y') ?: '—']) }}
                                    </div>
                                    @if(!empty($plan->cancellation_reason))
                                        <button type="button"
                                                class="btn btn--ghost btn--xs js-view-cancel-reason"
                                                data-customer="{{ $lead->name }}"
                                                data-reason="{{ $plan->cancellation_reason }}"
                                                style="margin-top:0.35rem;">
                                            {{ __('panel.customers.view_reason') }}
                                        </button>
                                    @endif
                                @endif
                            @else
                                <span class="text-muted text-sm">{{ __('panel.customers.no_maintenance_plan') }}</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @if($nextTask)
                                <span class="text-sm">{{ $nextTask->due_date->format('M j, Y') }}</span>
                                <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($nextTask->content, 60) }}</div>
                            @else
                                <span class="text-muted text-sm">{{ __('panel.customers.none_scheduled') }}</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 0.5rem;">
                            @if($chatUser)
                                <a href="{{ route('dealer.messages', ['with' => $chatUser->id]) }}" class="btn btn--primary btn--sm">{{ __('panel.customers.chat') }}</a>
                            @else
                                <span class="text-muted text-sm">{{ __('panel.customers.waiting_account') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:1rem;">{{ $customers->links() }}</div>
    @endif
</div>

<div class="modal-backdrop" id="cancelReasonModalDealer" role="dialog" aria-modal="true" aria-labelledby="cancelReasonModalDealerTitle">
    <div class="modal" style="width:min(520px,94vw)" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title" id="cancelReasonModalDealerTitle">{{ __('panel.customers.cancellation_reason') }}</div>
            <button type="button" class="modal-close" id="cancelReasonModalDealerClose" aria-label="{{ __('panel.common.close') }}">&times;</button>
        </div>
        <div class="modal-body">
            <div class="text-sm text-muted" id="cancelReasonModalDealerCustomer" style="margin-bottom:0.4rem;"></div>
            <div class="card" style="margin:0;padding:0.9rem;">
                <div id="cancelReasonModalDealerText" class="text-sm"></div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    (function () {
        function openCancelReasonDealer(customer, reason) {
            document.getElementById('cancelReasonModalDealerCustomer').textContent = customer ? @json(__('panel.customers.customer_label', ['name' => '___NAME___'])).replace('___NAME___', customer) : '';
            document.getElementById('cancelReasonModalDealerText').textContent = reason || @json(__('panel.customers.no_reason'));
            document.getElementById('cancelReasonModalDealer').classList.add('active');
        }
        function closeCancelReasonDealer() {
            document.getElementById('cancelReasonModalDealer').classList.remove('active');
        }
        var modalEl = document.getElementById('cancelReasonModalDealer');
        var closeBtnEl = document.getElementById('cancelReasonModalDealerClose');
        if (closeBtnEl) {
            closeBtnEl.addEventListener('click', function () {
                closeCancelReasonDealer();
            });
        }
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                if (e.target === modalEl) {
                    closeCancelReasonDealer();
                }
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCancelReasonDealer();
            }
        });
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.js-view-cancel-reason');
            if (trigger) {
                openCancelReasonDealer(trigger.getAttribute('data-customer'), trigger.getAttribute('data-reason'));
                return;
            }
        });
    })();
</script>
@endsection
@endsection
