@extends('layouts.customer')
@section('title', 'Dashboard – Customer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Dashboard</h1><p class="panel-page-sub">Welcome back. Manage your hot tub and service requests</p></div>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->has('reason')) <div class="alert alert--danger">{{ $errors->first('reason') }}</div> @endif

<div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Notifications</div>
<div class="card mb-4" style="padding:0;">
    <div id="notificationsList">
        @php
            $notifications = \App\Models\Notification::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(10)->get();
        @endphp
        @forelse($notifications as $notif)
            <div class="notification-item" style="padding:1rem; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; {{ !$notif->read ? 'background:#f0f9ff;' : '' }}">
                <div style="flex:1;">
                    <div class="text-sm {{ !$notif->read ? 'fw-700' : '' }}">{{ $notif->message }}</div>
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
                                <button class="btn btn--success btn--sm js-deposit-action" data-id="{{ $notif->id }}" data-lead="{{ $notif->data['lead_id'] }}" data-action="accept">Accept</button>
                                <button class="btn btn--danger-soft btn--sm js-deposit-action" data-id="{{ $notif->id }}" data-lead="{{ $notif->data['lead_id'] }}" data-action="reject">Reject</button>
                            </div>
                        @elseif($isWinner)
                            <div class="text-xs text-success fw-700 mt-1">✓ Accepted</div>
                        @elseif($isLoser)
                            <div class="text-xs text-danger fw-700 mt-1">✗ Rejected</div>
                        @endif
                    @endif
                </div>
                @if(!$notif->read)
                    <button class="btn btn--link btn--xs js-mark-read" data-id="{{ $notif->id }}">Mark as read</button>
                @endif
            </div>
        @empty
            <div class="text-sm text-muted text-center p-4">No notifications.</div>
        @endforelse
    </div>
</div>

<div class="grid" style="display:grid;grid-template-columns:1fr;gap:2rem;align-items:start">
    @forelse($leads as $lead)
    <div class="product-section" style="border-bottom: 2px solid #e5e7eb; padding-bottom: 2rem; margin-bottom: 1rem;">
        <div class="fw-800 mb-3" style="font-size:1.25rem;color:var(--gray-900); display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $lead->delivery_details['make'] ?? 'Product' }} {{ $lead->delivery_details['model'] ?? '' }}</span>
            @if($lead->stage === 'Delivered')
                <span class="badge badge--success" style="font-size: 0.85rem;">Delivered</span>
            @endif
        </div>

        <div class="grid grid--2 mb-4" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Unit Information</div>
                <div class="grid grid--2">
                    <div><div class="text-sm text-muted">Brand</div><div class="fw-700">{{ $lead->delivery_details['make'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Model</div><div class="fw-700">{{ $lead->delivery_details['model'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Shell Colour</div><div class="fw-700">{{ $lead->delivery_details['shell_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Cabinet Colour</div><div class="fw-700">{{ $lead->delivery_details['cabinet_colour'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Accessories</div><div class="fw-700">{{ $lead->delivery_details['accessories'] ?? '—' }}</div></div>
                    <div><div class="text-sm text-muted">Sale Price</div><div class="fw-700">{{ (isset($lead->delivery_details['sale_price'])) ? '£' . number_format($lead->delivery_details['sale_price'], 2) : '—' }}</div></div>
                </div>
            </div>

            <div class="card" style="margin:0; height: 100%;">
                <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Dealer / Manufacturer</div>
                @if($lead->dealer)
                    <div class="fw-700" style="font-size:1.1rem; color:var(--primary-600)">{{ $lead->dealer->company_name ?: $lead->dealer->name }}</div>
                    <div class="text-sm text-muted mt-1">{{ $lead->dealer->email }}</div>
                    <div class="text-sm text-muted">{{ $lead->dealer->phone }}</div>
                    <div class="text-sm text-muted mt-2">{{ $lead->dealer->address }}</div>
                @else
                    <div class="text-muted italic">Not linked to a specific dealer</div>
                @endif
            </div>
        </div>

        @if($lead->dealer && $lead->packages->count() > 0)
        <div class="fw-800 mb-2" style="font-size:1rem;color:var(--gray-900)">Maintenance Packages from {{ $lead->dealer->company_name ?: $lead->dealer->name }}</div>
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
                    <div style="position:absolute; top: 0; right: 0; background: #0ea5a3; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 0 8px 0 8px; text-transform: uppercase; letter-spacing: 0.5px;">MOST POPULAR</div>
                @endif
                @if($isActive)
                    <div style="position: absolute; top: 0; left: 0; background: #10b981; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Active</div>
                @elseif($isScheduledCancel)
                    <div style="position: absolute; top: 0; left: 0; background: #f59e0b; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Scheduled</div>
                @elseif($isCancelled)
                    <div style="position: absolute; top: 0; left: 0; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 8px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Cancelled</div>
                @endif
                <div class="fw-800" style="font-size:1.05rem; color:var(--gray-900); {{ $isActive ? 'margin-top: 12px;' : '' }}">{{ $pkg->name }}</div>
                <div class="fw-700 mt-1" style="font-size:1.2rem; color:var(--primary-600)">
                    £{{ number_format($pkg->price, 2) }}
                    <span style="font-size:.8rem;color:var(--gray-500);font-weight:600">/ {{ ($pkg->plan_type ?? 'yearly') === 'monthly' ? 'month' : 'year' }}</span>
                </div>
                <ul style="margin:10px 0; padding-left:18px; font-size:0.75rem; color:var(--gray-600); flex-grow:1">
                    @foreach($pkg->features ?? [] as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>
                @if($isActive)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        @if($requestStatus->start_date)Start: {{ $requestStatus->start_date->format('d M Y') }}<br>@endif
                        @if($requestStatus->expiry_date)Plan expires on: {{ $requestStatus->expiry_date->format('d M Y') }}<br>@endif
                        @if($remainingText){{ str_replace('from now', 'remaining', $remainingText) }}@endif
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <a href="{{ route('customer.messages') }}?dealer={{$lead->dealer->id}}" class="btn btn--primary btn--sm w-100">Chat</a>
                        <button type="button"
                                class="btn btn--outline btn--sm w-100 js-manage-subscription"
                                data-request-id="{{ $requestStatus->id }}"
                                data-plan-name="{{ $pkg->name }}">
                            Manage Subscription
                        </button>
                    </div>
                @elseif($isScheduledCancel)
                    <div class="text-xs" style="margin-top:-4px;margin-bottom:8px;color:#b45309;">
                        Scheduled for Cancellation<br>
                        Will cancel on {{ optional($requestStatus->cancellation_effective_at ?? $requestStatus->expiry_date)->format('d M Y') ?: '—' }}
                    </div>
                    @if(!empty($requestStatus->cancellation_reason))
                        <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                            Reason: {{ \Illuminate\Support\Str::limit($requestStatus->cancellation_reason, 90) }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('customer.package-requests.reactivate', $requestStatus->id) }}">
                        @csrf
                        <button type="submit" class="btn btn--primary btn--sm w-100 mt-2">Reactivate Plan</button>
                    </form>
                @elseif($isCancelled)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        Cancelled by Customer<br>
                        Cancellation Date: {{ optional($requestStatus->cancelled_at ?? $requestStatus->cancellation_effective_at)->format('d M Y') ?: '—' }}
                    </div>
                    @if(!empty($requestStatus->cancellation_reason))
                        <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                            Reason: {{ \Illuminate\Support\Str::limit($requestStatus->cancellation_reason, 90) }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('customer.package-requests.reactivate', $requestStatus->id) }}">
                        @csrf
                        <button type="submit" class="btn btn--primary btn--sm w-100 mt-2">Reactivate Plan</button>
                    </form>
                @elseif($isRequested)
                    <button class="btn btn--secondary btn--sm w-100 mt-2" disabled>Requested</button>
                @elseif($isExpired)
                    <div class="text-xs text-muted" style="margin-top:-4px;margin-bottom:8px;">
                        Expired on {{ optional($requestStatus->expiry_date)->format('d M Y') ?: '—' }}
                    </div>
                    <button class="btn btn--danger-soft btn--sm w-100 mt-2" disabled>Expired</button>
                @else
                    <button class="btn btn--primary btn--sm w-100 mt-2" onclick='openPackageModal(@json($pkg), @json($lead->id))'>Select Plan</button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="card">
        <div class="text-center text-muted py-4">No product details found yet.</div>
    </div>
    @endforelse
</div>

<div class="fw-800 mb-2 mt-4" style="font-size:1.05rem;color:var(--gray-900)">My Service Requests</div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead><tr><th>Request</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            @php
                $requests = \App\Models\ServiceRequest::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(5)->get();
            @endphp
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->product_name }}</td>
                    <td><span class="badge {{ $req->status === 'completed' ? 'badge--success' : '' }}">{{ ucfirst($req->status) }}</span></td>
                    <td>{{ $req->created_at->format('M d, Y') }}</td>
                    <td><button type="button" class="btn btn--ghost btn--sm" onclick='viewRequest(@json($req))'>View</button></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No service requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card mt-4">
    <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Recent Activity</div>
    <table class="table" id="recentActivity">
        <thead>
            <tr>
                <th>Activity</th>
                <th>Date</th>
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
                    <td colspan="2" class="text-center text-muted py-4">No recent activity.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">
        <button class="btn btn--outline btn--sm" id="seeMoreRecentActivity">See More</button>
        <button class="btn btn--outline btn--sm" id="seeLessRecentActivity" style="display: none;">See Less</button>
    </div>
</div>

{{-- View Modal (reused behavior from Service Requests page) --}}
<div id="viewModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn"
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none"
                onclick="document.getElementById('viewModal').style.display='none'">&times;</button>
        <h3 id="viewModalTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800; padding-right: 30px;">Request Details</h3>
        <div id="viewModalBody" style="margin-bottom:20px"></div>
        <div class="modal-actions" style="justify-content: flex-end;"><button class="btn btn--ghost btn--sm" onclick="document.getElementById('viewModal').style.display='none'">Close</button></div>
    </div>
</div>

{{-- Package Request Modal --}}
<div id="packageModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:500px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="document.getElementById('packageModal').style.display='none'">&times;</button>
        <h3 id="pkgModalTitle" style="margin-top:0; font-weight: 800;">Request Package</h3>
        
        <form method="POST" action="{{ route('customer.package-requests.store') }}">
            @csrf
            <input type="hidden" name="package_id" id="pkgIdInput">
            <input type="hidden" name="lead_id" id="leadIdInput">
            <div class="form-group">
                <label class="form-label">Additional Message (Optional)</label>
                <textarea name="message" class="form-input" rows="3" placeholder="Any specific requirements or questions?"></textarea>
            </div>
            <div class="modal-actions" style="justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('packageModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--primary btn--sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

{{-- Manage Subscription Modal --}}
<div id="subscriptionModal" class="modal" style="display:none;position:fixed;z-index:1200;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:min(560px,94vw);background:#fff;padding:24px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn" style="position:absolute;top:15px;right:15px;font-size:24px;border:none;background:none;cursor:pointer" onclick="closeSubscriptionModal()">&times;</button>
        <h3 style="margin-top:0; font-weight:800;">Cancel Subscription</h3>
        <p class="text-sm text-muted" id="subscriptionModalSubtitle" style="margin-top:-4px;"></p>
        <form id="subscriptionCancelForm" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label">Please tell us why you are cancelling</label>
                <textarea id="subscriptionCancelReason" name="reason" class="form-input" rows="4" placeholder="Write your reason..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Cancellation option</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;border:1px solid #fecaca;background:#fff1f2;padding:10px;border-radius:8px;cursor:pointer;">
                        <input type="radio" name="mode" value="immediate" class="js-cancel-mode">
                        <span class="text-sm" style="color:#b91c1c;font-weight:700;">Cancel immediately</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;background:#fff;padding:10px;border-radius:8px;cursor:pointer;">
                        <input type="radio" name="mode" value="scheduled" class="js-cancel-mode">
                        <span class="text-sm" style="font-weight:700;">Cancel at end of term</span>
                    </label>
                </div>
            </div>
            <div class="modal-actions" style="justify-content:flex-end;gap:10px;margin-top:20px;">
                <button type="button" class="btn btn--ghost btn--sm" onclick="closeSubscriptionModal()">Back</button>
                <button type="submit" id="confirmCancelBtn" class="btn btn--primary btn--sm" disabled>Confirm Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPackageModal(pkg, leadId) {
    document.getElementById('pkgModalTitle').textContent = 'Request ' + pkg.name;
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
    subtitle.textContent = planName ? ("Plan: " + planName) : '';
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
    document.getElementById('viewModalTitle').textContent = req.product_name || 'Request Details';
    document.getElementById('viewModalBody').innerHTML = `
        <div style="margin-bottom:10px"><span class="fw-700">Service Name:</span> ${req.product_name || 'N/A'}</div>
        <div style="margin-bottom:10px"><span class="fw-700">Status:</span> ${(req.status || 'N/A').toUpperCase()}</div>
        <div style="margin-bottom:10px"><span class="fw-700">Date:</span> ${req.created_at ? new Date(req.created_at).toLocaleDateString() : 'N/A'}</div>
        <div style="margin-bottom:10px"><span class="fw-700">Description:</span><br>${req.message || 'No message provided.'}</div>
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
            
            if (!confirm(`Are you sure you want to ${action} this deposit request?`)) return;
            
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
                    alert(data.msg || 'Something went wrong');
                }
            } catch(e) { alert('Network error'); }
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
