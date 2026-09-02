@extends('layouts.customer')
@section('title', __('panel.overview.title').' – '.__('panel.customer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.overview.title') }}</h1><p class="panel-page-sub">{{ __('panel.customer.dashboard_sub') }}</p></div>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->has('reason')) <div class="alert alert--danger">{{ $errors->first('reason') }}</div> @endif

<div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.notifications.title') }}</div>
<div class="card mb-4" style="padding:0;">
    <div id="notificationsList">
        @php
            $notifications = \App\Models\Notification::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(10)->get();
        @endphp
        @forelse($notifications as $notif)
            <div class="notification-item" style="padding:1rem; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; {{ !$notif->read ? 'background:#f0f9ff;' : '' }}">
                <div style="flex:1;">
                    <div class="text-sm {{ !$notif->read ? 'fw-700' : '' }}">{{ \App\Support\PanelTranslator::notificationMessage($notif) }}</div>
                    <div class="text-xs text-muted">{{ $notif->created_at->diffForHumans() }}</div>
                    
                    @if($notif->type === 'deposit_confirmation' && isset($notif->data['lead_id']))
                        @php 
                            $leadForNotif = \App\Models\Lead::find($notif->data['lead_id'] ?? 0); 
                            $notifDealerId = (int)($notif->data['dealer_id'] ?? 0);
                            $assignedDealerId = (int)($leadForNotif->assigned_dealer_id ?? 0);
                            $isWinner = ($leadForNotif && $leadForNotif->deposit_confirmed && $assignedDealerId === $notifDealerId);
                            $isLoser = ($leadForNotif && $leadForNotif->deposit_confirmed && $assignedDealerId !== 0 && $assignedDealerId !== $notifDealerId);
                        @endphp
                        @if($leadForNotif && !$leadForNotif->deposit_confirmed)
                            <div class="mt-2 d-flex gap-2">
                                <button class="btn btn--success btn--sm js-deposit-action" data-id="{{ $notif->id }}" data-lead="{{ $notif->data['lead_id'] }}" data-action="accept">{{ __('panel.customer_panel.accept') }}</button>
                                <button class="btn btn--danger-soft btn--sm js-deposit-action" data-id="{{ $notif->id }}" data-lead="{{ $notif->data['lead_id'] }}" data-action="reject">{{ __('panel.customer_panel.reject') }}</button>
                            </div>
                        @elseif($isWinner)
                            <div class="text-xs text-success fw-700 mt-1">✓ {{ __('panel.customer_panel.accepted') }}</div>
                        @elseif($isLoser)
                            <div class="text-xs text-danger fw-700 mt-1">✗ {{ __('panel.customer_panel.rejected') }}</div>
                        @endif
                    @endif
                </div>
                @if(!$notif->read)
                    <button class="btn btn--link btn--xs js-mark-read" data-id="{{ $notif->id }}">{{ __('panel.customer_panel.mark_as_read') }}</button>
                @endif
            </div>
        @empty
            <div class="text-sm text-muted text-center p-4">{{ __('panel.customer_panel.no_notifications') }}</div>
        @endforelse
    </div>
</div>

<div class="grid" style="display:grid;grid-template-columns:1fr;gap:2rem;align-items:start">
    @forelse($leads as $lead)
    <div class="product-section" style="border-bottom: 2px solid #e5e7eb; padding-bottom: 2rem; margin-bottom: 1rem;">
        <div class="fw-800 mb-3" style="font-size:1.25rem;color:var(--gray-900); display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $lead->delivery_details['make'] ?? __('panel.customer_panel.product') }} {{ $lead->delivery_details['model'] ?? '' }}</span>
            @if($lead->stage === 'Delivered')
                <span class="badge badge--success" style="font-size: 0.85rem;">{{ __('panel.customer_panel.delivered') }}</span>
            @endif
        </div>

        <div class="grid grid--2 mb-4" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">{{ __('panel.customer_panel.unit_information') }}</div>
                <div class="grid grid--2">
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.brand') }}</div><div class="fw-700">{{ $lead->delivery_details['make'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.model') }}</div><div class="fw-700">{{ $lead->delivery_details['model'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.shell_colour') }}</div><div class="fw-700">{{ $lead->delivery_details['shell_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.cabinet_colour') }}</div><div class="fw-700">{{ $lead->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.accessories') }}</div><div class="fw-700">{{ $lead->delivery_details['accessories'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">{{ __('panel.customer_panel.sale_price') }}</div><div class="fw-700">{{ (isset($lead->delivery_details['sale_price'])) ? '£' . number_format($lead->delivery_details['sale_price'], 2) : '—' }}</div></div>
                </div>
            </div>

            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">{{ __('panel.customer_panel.dealer_manufacturer') }}</div>
                @if($lead->dealer)
                    <div class="fw-700" style="font-size:1.1rem; color:var(--primary-600)">{{ $lead->dealer->businessDisplayName() }}</div>
                    <div class="text-sm text-muted mt-1">{{ $lead->dealer->email }}</div>
                    <div class="text-sm text-muted">{{ $lead->dealer->phone }}</div>
                    <div class="text-sm text-muted mt-2">{{ $lead->dealer->address }}</div>
                @else
                    <div class="text-muted italic">{{ __('panel.customer_panel.not_linked_dealer') }}</div>
                @endif
            </div>
        </div>

        @if($lead->dealer && $lead->packages->count() > 0)
        <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">{{ __('panel.customer_panel.maintenance_packages_from', ['dealer' => $lead->dealer->businessDisplayName()]) }}</div>
        <div class="grid grid--3 mb-4">
            @foreach($lead->packages as $pkg)
            @php
                $requestStatus = ($pkg->requests ?? collect())
                    ->where('user_id', auth()->id())
                    ->sortByDesc('created_at')
                    ->first();
                $isActive = $requestStatus && $requestStatus->status === 'active';
                $isRequested = $requestStatus && $requestStatus->status === 'pending';
                $isExpired = $requestStatus && $requestStatus->status === 'expired';
                $isScheduledCancel = $requestStatus && $requestStatus->status === 'cancellation_scheduled';
                $isCancelled = $requestStatus && $requestStatus->status === 'cancelled';
                $remainingText = null;
                if ($isActive && $requestStatus->expiry_date) {
                    $remainingText = now()->diffForHumans($requestStatus->expiry_date, [
                        'parts' => 2,
                        'short' => true,
                        'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
                    ]);
                }
            @endphp
            <div class="card {{ $isActive ? 'plan--active' : '' }}" style="display:flex; flex-direction:column; margin-bottom: 0; position: relative;">
                @if($pkg->is_most_popular)
                    <div style="position:absolute; top: 0; right: 0; background: #0ea5a3; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 0 8px 0 8px; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('panel.customer_panel.most_popular') }}</div>
                @endif
                @if($isActive)
                    <div style="position: absolute; top: 0; left: 0; background: #10b981; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('panel.customer_panel.active') }}</div>
                @elseif($isScheduledCancel)
                    <div style="position: absolute; top: 0; left: 0; background: #f59e0b; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('panel.customer_panel.scheduled') }}</div>
                @elseif($isCancelled)
                    <div style="position: absolute; top: 0; left: 0; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('panel.customer_panel.cancelled') }}</div>
                @endif
                <div class="fw-800" style="font-size:1.05rem; color:var(--gray-900); {{ $isActive ? 'margin-top: 12px;' : '' }}">{{ $pkg->name }}</div>
                <div class="fw-700 mt-1" style="font-size:1.2rem; color:var(--primary-600)">
                    £{{ number_format($pkg->price, 2) }}
                    <span style="font-size:.8rem;color:var(--gray-500);font-weight:600">/ {{ ($pkg->plan_type ?? 'yearly') === 'monthly' ? __('panel.customer_panel.month') : __('panel.customer_panel.year') }}</span>
                </div>
                <ul style="margin:10px 0; padding-left:18px; font-size:0.75rem; color:var(--gray-600); flex-grow:1">
                    @foreach($pkg->features ?? [] as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>
                @if($isActive)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        @if($requestStatus->start_date){{ __('panel.customer_panel.start') }}: {{ $requestStatus->start_date->format('d M Y') }}<br>@endif
                        @if($requestStatus->expiry_date){{ __('panel.customer_panel.plan_expires_on') }}: {{ $requestStatus->expiry_date->format('d M Y') }}<br>@endif
                        @if($remainingText){{ str_replace(__('panel.customer_panel.from_now'), __('panel.customer_panel.remaining'), $remainingText) }}@endif
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <a href="{{ route('customer.messages') }}?dealer={{$lead->dealer->id}}" class="btn btn--primary btn--sm w-100">{{ __('panel.customer_panel.chat') }}</a>
                        <button type="button"
                                class="btn btn--outline btn--sm w-100 js-manage-subscription"
                                data-request-id="{{ $requestStatus->id }}"
                                data-plan-name="{{ $pkg->name }}">
                            {{ __('panel.customer_panel.manage_subscription') }}
                        </button>
                    </div>
                @elseif($isScheduledCancel)
                    <div class="text-xs" style="margin-top:-4px;margin-bottom:8px;color:#b45309;">
                        {{ __('panel.customer_panel.scheduled_for_cancellation') }}<br>
                        {{ __('panel.customer_panel.will_cancel_on') }} {{ optional($requestStatus->cancellation_effective_at ?? $requestStatus->expiry_date)->format('d M Y') ?: '—' }}
                    </div>
                    @if(!empty($requestStatus->cancellation_reason))
                        <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                            {{ __('panel.customer_panel.reason') }}: {{ \Illuminate\Support\Str::limit($requestStatus->cancellation_reason, 90) }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('customer.package-requests.reactivate', $requestStatus->id) }}">
                        @csrf
                        <button type="submit" class="btn btn--primary btn--sm w-100 mt-2">{{ __('panel.customer_panel.reactivate_plan') }}</button>
                    </form>
                @elseif($isCancelled)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        {{ __('panel.customer_panel.cancelled_by_customer') }}<br>
                        {{ __('panel.customer_panel.cancellation_date') }}: {{ optional($requestStatus->cancelled_at ?? $requestStatus->cancellation_effective_at)->format('d M Y') ?: '—' }}
                    </div>
                    @if(!empty($requestStatus->cancellation_reason))
                        <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                            {{ __('panel.customer_panel.reason') }}: {{ \Illuminate\Support\Str::limit($requestStatus->cancellation_reason, 90) }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('customer.package-requests.reactivate', $requestStatus->id) }}">
                        @csrf
                        <button type="submit" class="btn btn--primary btn--sm w-100 mt-2">{{ __('panel.customer_panel.reactivate_plan') }}</button>
                    </form>
                @elseif($isRequested)
                    <button class="btn btn--secondary btn--sm w-100 mt-2" disabled>{{ __('panel.customer_panel.requested') }}</button>
                @elseif($isExpired)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        {{ __('panel.customer_panel.expired_on') }} {{ optional($requestStatus->expiry_date)->format('d M Y') ?: '—' }}
                    </div>
                    <button class="btn btn--danger-soft btn--sm w-100 mt-2" disabled>{{ __('panel.customer_panel.expired') }}</button>
                @else
                    <button class="btn btn--primary btn--sm w-100 mt-2" onclick='openPackageModal(@json($pkg), @json($lead->id))'>{{ __('panel.customer_panel.select_plan') }}</button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="card">
        <div class="text-center text-muted py-4">{{ __('panel.customer_panel.no_product_details') }}</div>
    </div>
    @endforelse
</div>

<div class="fw-800 mb-2 mt-4" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.customer_panel.my_service_requests') }}</div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead><tr><th>{{ __('panel.customer_panel.request') }}</th><th>{{ __('panel.customer_panel.status') }}</th><th>{{ __('panel.customer_panel.date') }}</th><th>{{ __('panel.customer_panel.actions') }}</th></tr></thead>
        <tbody>
            @php
                $requests = \App\Models\ServiceRequest::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(5)->get();
            @endphp
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->product_name }}</td>
                    <td><span class="badge {{ $req->status === 'completed' ? 'badge--success' : '' }}">{{ ucfirst($req->status) }}</span></td>
                    <td>{{ $req->created_at->format('M d, Y') }}</td>
                    <td><button type="button" class="btn btn--ghost btn--sm" onclick='viewRequest(@json($req))'>{{ __('panel.customer_panel.view') }}</button></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('panel.customer_panel.no_service_requests_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card mt-4">
    <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.customer_panel.recent_activity') }}</div>
    <table class="table" id="recentActivity">
        <thead>
            <tr>
                <th>{{ __('panel.customer_panel.activity') }}</th>
                <th>{{ __('panel.customer_panel.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentActivity as $activity)
                <tr>
                    <td>{{ $activity->message }}</td>
                    <td>{{ $activity->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted py-4">{{ __('panel.customer_panel.no_recent_activity') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">
        <button class="btn btn--outline btn--sm" id="seeMoreRecentActivity">{{ __('panel.customer_panel.see_more') }}</button>
        <button class="btn btn--outline btn--sm" id="seeLessRecentActivity" style="display: none;">{{ __('panel.customer_panel.see_less') }}</button>
    </div>
</div>

{{-- View Modal (reused behavior from Service Requests page) --}}
<div id="viewModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn"
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none"
                onclick="document.getElementById('viewModal').style.display='none'">&times;</button>
        <h3 id="viewModalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">{{ __('panel.customer_panel.request_details') }}</h3>
        <div id="viewModalBody" style="margin-bottom:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end;"><button class="btn btn--ghost btn--sm" onclick="document.getElementById('viewModal').style.display='none'">{{ __('panel.customer_panel.close') }}</button></div>
    </div>
</div>

{{-- Package Request Modal --}}
<div id="packageModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="document.getElementById('packageModal').style.display='none'">&times;</button>
        <h3 id="pkgModalTitle" style="margin-top:0; font-weight: 800;">{{ __('panel.customer_panel.request_package') }}</h3>
        
        <form method="POST" action="{{ route('customer.package-requests.store') }}">
            @csrf
            <input type="hidden" name="package_id" id="pkgIdInput">
            <input type="hidden" name="lead_id" id="leadIdInput">
            <div class="form-group">
                <label class="form-label">{{ __('panel.customer_panel.additional_message_optional') }}</label>
                <textarea name="message" class="form-input" rows="3" placeholder="{{ __('panel.customer_panel.package_message_placeholder') }}"></textarea>
            </div>
            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('packageModal').style.display='none'">{{ __('panel.customer_panel.cancel') }}</button>
                <button type="submit" class="btn btn--primary btn--sm">{{ __('panel.customer_panel.submit_request') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Manage Subscription Modal --}}
<div id="subscriptionModal" class="modal" style="display:none;position:fixed;z-index:1200;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:min(560px,94vw);background:#fff;padding:24px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="closeSubscriptionModal()">&times;</button>
        <h3 style="margin-top:0; font-weight:800;">{{ __('panel.customer_panel.cancel_subscription') }}</h3>
        <p class="text-sm text-muted" id="subscriptionModalSubtitle" style="margin-top:-4px;"></p>
        <form id="subscriptionCancelForm" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('panel.customer_panel.cancel_reason_label') }}</label>
                <textarea id="subscriptionCancelReason" name="reason" class="form-input" rows="4" placeholder="{{ __('panel.customer_panel.cancel_reason_placeholder') }}" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.customer_panel.cancellation_option') }}</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;border:1px solid #fecaca;background:#fff1f2;padding:10px;border-radius:8px;cursor:pointer;">
                        <input type="radio" name="mode" value="immediate" class="js-cancel-mode">
                        <span class="text-sm" style="color:#b91c1c;font-weight:700;">{{ __('panel.customer_panel.cancel_immediately') }}</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;background:#fff;padding:10px;border-radius:8px;cursor:pointer;">
                        <input type="radio" name="mode" value="scheduled" class="js-cancel-mode">
                        <span class="text-sm" style="font-weight:700;">{{ __('panel.customer_panel.cancel_end_of_term') }}</span>
                    </label>
                </div>
            </div>
            <div class="modal-actions" style="justify-content:flex-end;gap:10px;margin-top:20px;">
                <button type="button" class="btn btn--ghost btn--sm" onclick="closeSubscriptionModal()">{{ __('panel.customer_panel.back') }}</button>
                <button type="submit" id="confirmCancelBtn" class="btn btn--primary btn--sm" disabled>{{ __('panel.customer_panel.confirm_cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@php
    $overviewPanelI18n = [
        'request' => __('panel.customer_panel.request'),
        'requestDetails' => __('panel.customer_panel.request_details'),
        'requestPackage' => __('panel.customer_panel.request_package'),
        'plan' => __('panel.customer_panel.plan'),
        'serviceName' => __('panel.customer_panel.service_name'),
        'status' => __('panel.customer_panel.status'),
        'date' => __('panel.customer_panel.date'),
        'description' => __('panel.customer_panel.description'),
        'noMessage' => __('panel.customer_panel.no_message_provided'),
        'na' => __('panel.customer_panel.na'),
        'type' => __('panel.customer_panel.type'),
        'submitted' => __('panel.customer_panel.submitted'),
        'message' => __('panel.customer_panel.message'),
        'yourSignature' => __('panel.customer_panel.your_signature'),
        'clickToEnlarge' => __('panel.customer_panel.click_to_enlarge'),
        'accept' => __('panel.customer_panel.accept'),
        'reject' => __('panel.customer_panel.reject'),
        'confirmDepositPrompt' => __('panel.customer_panel.confirm_deposit_prompt'),
        'somethingWentWrong' => __('panel.customer_panel.something_went_wrong'),
        'networkError' => __('panel.common.network_error'),
        'accepted' => __('panel.customer_panel.accepted'),
        'rejected' => __('panel.customer_panel.rejected'),
    ];
@endphp

<script>
const panelI18n = @json($overviewPanelI18n);

function openPackageModal(pkg, leadId) {
    document.getElementById('pkgModalTitle').textContent = panelI18n.request + ' ' + pkg.name;
    document.getElementById('pkgIdInput').value = pkg.id;
    document.getElementById('leadIdInput').value = leadId;
    document.getElementById('packageModal').style.display = 'flex';
}

function openSubscriptionModal(requestId, planName) {
    var modal = document.getElementById('subscriptionModal');
    var form = document.getElementById('subscriptionCancelForm');
    var reason = document.getElementById('subscriptionCancelReason');
    var subtitle = document.getElementById('subscriptionModalSubtitle');
    var confirmBtn = document.getElementById('confirmCancelBtn');
    form.action = "{{ url('/customer/package-requests') }}/" + requestId + "/cancel";
    subtitle.textContent = planName ? (panelI18n.plan + ': ' + planName) : '';
    reason.value = '';
    document.querySelectorAll('.js-cancel-mode').forEach(function(input) {
        input.checked = false;
    });
    confirmBtn.disabled = true;
    modal.style.display = 'flex';
}

function closeSubscriptionModal() {
    document.getElementById('subscriptionModal').style.display = 'none';
}

function viewRequest(req) {
    document.getElementById('viewModalTitle').textContent = req.product_name || panelI18n.requestDetails;
    document.getElementById('viewModalBody').innerHTML = `
        <div style="margin-bottom:10px"><span class="fw-700">${panelI18n.serviceName}:</span> ${req.product_name || panelI18n.na}</div>
        <div style="margin-bottom:10px"><span class="fw-700">${panelI18n.status}:</span> ${(req.status || panelI18n.na).toUpperCase()}</div>
        <div style="margin-bottom:10px"><span class="fw-700">${panelI18n.date}:</span> ${req.created_at ? new Date(req.created_at).toLocaleDateString() : panelI18n.na}</div>
        <div style="margin-bottom:10px"><span class="fw-700">${panelI18n.description}:</span><br>${req.message || panelI18n.noMessage}</div>
    `;
    document.getElementById('viewModal').style.display = 'flex';
}
</script>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.querySelectorAll('.js-deposit-action').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const lead = this.getAttribute('data-lead');
            const action = this.getAttribute('data-action');
            
            const actionLabel = action === 'accept' ? panelI18n.accept : panelI18n.reject;
            if (!confirm(panelI18n.confirmDepositPrompt.replace(':action', actionLabel))) return;
            
            try {
                const res = await fetch('{{ route('customer.deposit.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ notification_id: id, action: action })
                });
                const data = await res.json();
                if (res.ok && data.ok) {
                    alert(data.msg);
                    window.location.reload();
                } else {
                    alert(data.msg || panelI18n.somethingWentWrong);
                }
            } catch(e) { alert(panelI18n.networkError); }
        });
    });

    document.querySelectorAll('.js-mark-read').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const res = await fetch('{{ route('customer.notifications.read') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: id })
                });
                if (res.ok) {
                    this.closest('.notification-item').style.background = 'transparent';
                    this.remove();
                }
            } catch(e) {}
        });
    });

    $(document).ready(function(){
        function updateCancelConfirmState() {
            var reason = ($('#subscriptionCancelReason').val() || '').trim();
            var mode = $('.js-cancel-mode:checked').length > 0;
            $('#confirmCancelBtn').prop('disabled', !(reason.length > 0 && mode));
        }

        $(document).on('click', '.js-manage-subscription', function () {
            openSubscriptionModal($(this).data('request-id'), $(this).data('plan-name'));
        });

        $(document).on('input change', '#subscriptionCancelReason, .js-cancel-mode', function () {
            updateCancelConfirmState();
        });

        $('#subscriptionModal').on('click', function(e){
            if (e.target === this) closeSubscriptionModal();
        });

        var recentActivityPage = 1;
        var recentActivityInitialItems = 5;
        var recentActivityTotalItems = 0;

        function loadRecentActivity(){
            $.ajax({
                url: "{{ route('customer.overview') }}" + "?page=" + recentActivityPage,
                type: 'get',
                success: function(response){
                    var items = $(response).find('#recentActivity tbody tr');
                    recentActivityTotalItems += items.length;
                    $('#recentActivity tbody').append(items);

                    if(recentActivityTotalItems >= recentActivityInitialItems * 2){
                        $('#seeMoreRecentActivity').hide();
                        $('#seeLessRecentActivity').show();
                    } else if (items.length < recentActivityInitialItems) {
                        $('#seeMoreRecentActivity').hide();
                    }
                }
            });
        }

        $('#seeMoreRecentActivity').on('click', function(){
            recentActivityPage++;
            loadRecentActivity();
        });

        $('#seeLessRecentActivity').on('click', function(){
            recentActivityPage = 1;
            recentActivityTotalItems = 0;
            $('#recentActivity tbody').html('');
            loadRecentActivity();
            $('#seeMoreRecentActivity').show();
            $('#seeLessRecentActivity').hide();
        });
    });
</script>
@endsection
