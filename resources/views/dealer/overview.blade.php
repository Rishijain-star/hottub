@extends('layouts.dealer')
@section('title', 'Overview – Dealer Panel')
@section('styles')
<style>.steps{display:flex;flex-direction:column;gap:.6rem}.step{display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;border:1px solid #e3edff;background:#f5f9ff;border-radius:var(--r-lg);font-weight:600;color:#1e3a8a}.step__num{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#2563eb;color:#fff;font-size:.8rem}.step__text{flex:1}.task-status-badge{display:inline-flex;align-items:center;padding:.2rem .5rem;border-radius:999px;font-size:.72rem;font-weight:700;line-height:1}.task-status-badge--active{background:#ecfdf5;color:#047857}.task-status-badge--won{background:#dcfce7;color:#166534}.task-status-badge--closed{background:#fee2e2;color:#b91c1c}.dealer-overview-split{display:grid;gap:1.25rem}@media (min-width:992px){.dealer-overview-split{grid-template-columns:1fr minmax(260px,320px);align-items:start}.dealer-overview-tasks{position:sticky;top:1rem}}#recentActivityModal .modal-body{max-height:min(70vh,520px)}</style>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        var initialTaskItems = 15;
        var taskEndpoint = "{{ route('dealer.dashboard.tasks') }}";
        var taskRefreshMs = 15000;
        var recentActivityUrl = "{{ route('dealer.overview.recent-activity') }}";
        var serverHasMoreTasks = @json($dashboardTasksHasMore ?? false);
        var isTasksExpanded = false;

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function taskRowHtml(task) {
            var due = task.due_date ? (' · Due ' + escapeHtml(task.due_date)) : '';
            var content = task.content || '';
            var shortContent = content.length > 80 ? (content.substring(0, 80) + '...') : content;
            var statusClass = task.status_class || 'active';
            var statusLabel = task.status_label || 'Active';
            return '<li class="dashboard-task-item" data-url="' + escapeHtml(task.lead_url) + '" style="padding:0.7rem 0.85rem;border:1px solid #e5e7eb;border-radius:8px;font-size:0.88rem;background:#fff;cursor:pointer;transition:box-shadow .15s ease,border-color .15s ease;">'
                + '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">'
                + '<div class="fw-700" style="color:var(--gray-900)">' + escapeHtml(shortContent) + '</div>'
                + '<span class="task-status-badge task-status-badge--' + escapeHtml(statusClass) + '">' + escapeHtml(statusLabel) + '</span>'
                + '</div>'
                + '<div class="text-xs text-muted mt-1">Lead #' + escapeHtml(String(task.lead_id)) + due + '</div>'
                + '</li>';
        }

        function bindTaskCardClick() {
            $('#dashboardTasksList .dashboard-task-item[data-url]').off('click').on('click', function () {
                var targetUrl = $(this).data('url');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        }

        function applyTaskVisibility() {
            var $taskItems = $('#dashboardTasksList .dashboard-task-item');
            var total = $taskItems.length;
            var hasMore = serverHasMoreTasks || total > initialTaskItems;

            if (!hasMore) {
                $taskItems.show();
                $('#dashboardTasksActions').hide();
                return;
            }

            if (!isTasksExpanded && total <= initialTaskItems && serverHasMoreTasks) {
                $taskItems.show();
                $('#dashboardTasksActions').show();
                $('#seeMoreDashboardTasks').show();
                $('#seeLessDashboardTasks').hide();
                return;
            }

            if (!isTasksExpanded && total > initialTaskItems) {
                $taskItems.each(function(index){
                    $(this).toggle(index < initialTaskItems);
                });
                $('#dashboardTasksActions').show();
                $('#seeMoreDashboardTasks').show();
                $('#seeLessDashboardTasks').hide();
                return;
            }

            $taskItems.show();
            $('#dashboardTasksActions').show();
            $('#seeMoreDashboardTasks').hide();
            $('#seeLessDashboardTasks').show();
        }

        function renderDashboardTasks(tasks) {
            var $list = $('#dashboardTasksList');
            if (!tasks || tasks.length === 0) {
                $list.html('<li class="text-sm text-muted">No upcoming tasks for the next few days.</li>');
                $('#dashboardTasksActions').hide();
                return;
            }

            $list.html(tasks.map(taskRowHtml).join(''));
            bindTaskCardClick();
            applyTaskVisibility();
        }

        function refreshDashboardTasks() {
            if (isTasksExpanded) {
                return;
            }
            $.ajax({
                url: taskEndpoint,
                type: 'get',
                cache: false,
                success: function(response){
                    if (response && response.ok) {
                        renderDashboardTasks(response.tasks || []);
                    }
                }
            });
        }

        $('#seeMoreDashboardTasks').on('click', function(){
            var $btn = $(this);
            if ($btn.data('loading')) {
                return;
            }
            if (isTasksExpanded) {
                return;
            }
            $btn.data('loading', true);
            $.getJSON(taskEndpoint, { additional: 1 })
                .done(function(res){
                    $btn.data('loading', false);
                    if (res && res.ok && res.tasks && res.tasks.length) {
                        $('#dashboardTasksList').append(res.tasks.map(taskRowHtml).join(''));
                        bindTaskCardClick();
                    }
                    isTasksExpanded = true;
                    applyTaskVisibility();
                })
                .fail(function(){
                    $btn.data('loading', false);
                });
        });

        $('#seeLessDashboardTasks').on('click', function(){
            isTasksExpanded = false;
            refreshDashboardTasks();
        });

        function openRecentActivityModal() {
            var $backdrop = $('#recentActivityModal');
            var $tbody = $('#recentActivityModalBody');
            var $table = $('#recentActivityModalTable');
            var $empty = $('#recentActivityModalEmpty');
            $tbody.html('<tr><td colspan="2" class="text-muted text-sm py-3">Loading…</td></tr>');
            $table.show();
            $empty.hide();
            $backdrop.addClass('active');
            $.getJSON(recentActivityUrl)
                .done(function(res){
                    $tbody.empty();
                    if (!res || !res.ok || !res.items || !res.items.length) {
                        $table.hide();
                        $empty.show();
                        return;
                    }
                    res.items.forEach(function(row){
                        $tbody.append(
                            '<tr><td>' + escapeHtml(row.message) + '</td><td class="text-muted text-sm">' + escapeHtml(row.date) + '</td></tr>'
                        );
                    });
                })
                .fail(function(){
                    $tbody.html('<tr><td colspan="2" class="text-danger text-sm py-3">Could not load activity.</td></tr>');
                });
        }

        function closeRecentActivityModal() {
            $('#recentActivityModal').removeClass('active');
        }

        function publicMediaUrlClient(rel) {
            if (!rel) return '';
            var s = String(rel).replace(/\\/g, '/').trim();
            s = s.replace(/\/storage\/app\/public\//gi, '/uploads/app/public/').replace(/\/storage\//gi, '/uploads/app/public/');
            s = s.replace(/\/uploads\/(?!app\/public\/)/gi, '/uploads/app/public/');
            if (/^https?:\/\//i.test(s)) return s;
            if (s.startsWith('/uploads/') || s.startsWith('/images/')) return s;
            s = s.replace(/^\/+/, '');
            var low = s.toLowerCase();
            while (low.indexOf('storage/app/public/') === 0) {
                s = s.substring(19);
                low = s.toLowerCase();
            }
            if (low.indexOf('public/storage/') === 0) s = s.substring(15);
            low = s.toLowerCase();
            if (low.indexOf('storage/') === 0 && low.indexOf('storage/app/') !== 0) s = s.substring(8);
            low = s.toLowerCase();
            while (low.indexOf('uploads/') === 0) { s = s.substring(8); low = s.toLowerCase(); }
            while (low.indexOf('app/public/') === 0) { s = s.substring(11); low = s.toLowerCase(); }
            if (low.indexOf('images/') === 0) return '/' + s;
            return '/uploads/app/public/' + s;
        }

        window.openImagePreview = function(src) {
            document.getElementById('previewImage').src = src;
            document.getElementById('imagePreviewModal').style.display = 'flex';
        };

        window.viewHistoryDetails = function(req) {
            document.getElementById('historyTitle').textContent = 'Service History: ' + req.product_name;
            var data = req.checklist_data || {};
            document.getElementById('historyBody').innerHTML = `
                <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                    <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Work Checklist:</h4>
                    <div class="text-sm text-muted">
                        <div style="margin-bottom:5px"><strong>Type:</strong> ${data.service_type || 'N/A'}</div>
                        <div style="margin-bottom:5px"><strong>Date:</strong> ${data.service_date || 'N/A'}</div>
                        <div style="margin-bottom:5px"><strong>Summary:</strong> ${data.work_summary || 'N/A'}</div>
                        <div style="margin-bottom:5px"><strong>Parts:</strong> ${data.parts_replaced || 'None'}</div>
                        <div style="margin-bottom:5px"><strong>Dealer Notes:</strong> ${data.notes || 'None'}</div>
                    </div>
                </div>
                <div style="margin-bottom:15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                    <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Customer Feedback:</h4>
                    <div class="text-sm text-muted">${req.customer_review || 'No review provided.'}</div>
                </div>
                <div>
                    <h4 style="margin:0 0 10px 0; font-size:0.95rem; color:var(--gray-900)">Customer Signature:</h4>
                    ${req.customer_signature ? `
                        <div style="cursor:pointer;" onclick="openImagePreview(${JSON.stringify(publicMediaUrlClient(req.customer_signature))})">
                            <img src=${JSON.stringify(publicMediaUrlClient(req.customer_signature))} alt="Signature" style="max-width: 200px; border: 1px solid #eee; border-radius: 4px;"/>
                            <p style="font-size:10px; color:var(--gray-500); margin-top:4px;">Click to enlarge</p>
                        </div>
                    ` : '<div class="text-sm text-muted">N/A</div>'}
                </div>
            `;
            document.getElementById('historyModal').style.display = 'flex';
        };

        $('#seeMoreRecentActivity').on('click', function(){
            openRecentActivityModal();
        });

        $('#recentActivityModalClose').on('click', function(){
            closeRecentActivityModal();
        });

        $('#recentActivityModal').on('click', function(e){
            if (e.target === this) {
                closeRecentActivityModal();
            }
        });

        $(document).on('keydown', function(e){
            if (e.key === 'Escape') {
                closeRecentActivityModal();
            }
        });

        bindTaskCardClick();
        applyTaskVisibility();
        setInterval(refreshDashboardTasks, taskRefreshMs);
    });
</script>
@endsection
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Dashboard</h1><p class="panel-page-sub">Welcome back. Track your credits, leads and performance</p></div>
    </div>
@php($me = auth()->user())
<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#ecfdf5;"><svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9a4 4 0 0 0 0 8h6a4 4 0 0 1 0 8H6"/></svg></div>
        <div class="panel-stat-card__label">Available Credits</div>
        <div class="panel-stat-card__value">{{ $availableCredits }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#eff6ff;"><svg width="22" height="22" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="panel-stat-card__label">Available Leads</div>
        <div class="panel-stat-card__value">{{ $availableLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#faf5ff;"><svg width="22" height="22" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><path d="M8 17l4-4 4 4M12 12V3"/></svg></div>
        <div class="panel-stat-card__label">Purchased Leads</div>
        <div class="panel-stat-card__value">{{ $purchasedLeadsCount }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fff7ed;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <div class="panel-stat-card__label">Active Leads</div>
        <div class="panel-stat-card__value">{{ $activeLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#dcfce7;"><svg width="22" height="22" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="panel-stat-card__label">Won / Converted</div>
        <div class="panel-stat-card__value">{{ $convertedLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fee2e2;"><svg width="22" height="22" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="panel-stat-card__label">Lost Leads</div>
        <div class="panel-stat-card__value">{{ $lostLeads }}</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fef9c3;"><svg width="22" height="22" fill="none" stroke="#ca8a04" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 2 2 5-5"/></svg></div>
        <div class="panel-stat-card__label">Conversion %</div>
        <div class="panel-stat-card__value">{{ $conversionRate }}%</div>
    </div>
</div>
<div class="dealer-overview-split" style="margin-bottom:1.5rem;margin-top:1.5rem">
<div>
<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Recent Requests from Customers
        @if($recentRequests->count() > 0)
            <span class="text-muted" style="font-size:.9rem;font-weight:600">({{ $recentRequests->count() }})</span>
        @endif
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentRequests as $request)
                <tr>
                    <td>{{ $request->product_name }}</td>
                    <td>{{ $request->customer->name }}</td>
                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                    <td><span class="badge {{ $request->status === 'completed' ? 'badge--success' : '' }}">{{ ucfirst($request->status) }}</span></td>
                    <td>
                        @if($request->status === 'completed')
                            <button type="button" class="btn btn--ghost btn--sm" onclick='viewHistoryDetails(@json($request))'>View</button>
                        @else
                            <a href="{{ route('dealer.service-requests') }}" class="btn btn--ghost btn--sm">View</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No recent requests.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card mt-4">
    <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Recent Activity
        @if(($maintenanceActivityUnreadCount ?? 0) > 0)
            <span class="notification-badge" title="New maintenance plan activity">{{ $maintenanceActivityUnreadCount }}</span>
        @endif
    </div>
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
        @if(($recentActivityTotalCount ?? 0) > 5)
        <button type="button" class="btn btn--outline btn--sm" id="seeMoreRecentActivity">See More</button>
        @endif
        <button type="button" class="btn btn--outline btn--sm" id="seeLessRecentActivity" style="display:none" aria-hidden="true" tabindex="-1">See Less</button>
    </div>
</div>
</div>
<aside class="card dealer-overview-tasks" style="margin-top:0">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Tasks &amp; actions</div>
    <p class="text-sm text-muted" style="margin-bottom:0.9rem">Outstanding tasks for active and won leads</p>
    @php($dashboardTaskItems = $dashboardTasks ?? collect())
    <ul id="dashboardTasksList" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.75rem">
        @forelse($dashboardTaskItems as $task)
        @php($isWonTask = (($task->lead?->status ?? null) === 'converted' && (int) ($task->lead?->assigned_dealer_id ?? 0) === (int) auth()->id()))
        @php($isClosedTask = (($task->lead?->status ?? null) === 'converted' && !$isWonTask))
        @php($taskStatusLabel = $isWonTask ? 'Won' : ($isClosedTask ? 'Closed' : 'Active'))
        @php($taskStatusClass = $isWonTask ? 'won' : ($isClosedTask ? 'closed' : 'active'))
        <li class="dashboard-task-item" data-url="{{ route('dealer.leads.view', $task->lead_id) }}" style="padding:0.7rem 0.85rem;border:1px solid #e5e7eb;border-radius:8px;font-size:0.88rem;background:#fff;cursor:pointer;transition:box-shadow .15s ease,border-color .15s ease;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">
                <div class="fw-700" style="color:var(--gray-900)">{{ \Illuminate\Support\Str::limit($task->content, 80) }}</div>
                <span class="task-status-badge task-status-badge--{{ $taskStatusClass }}">{{ $taskStatusLabel }}</span>
            </div>
            <div class="text-xs text-muted mt-1">
                Lead #{{ $task->lead_id }}
                @if($task->due_date)
                    · Due {{ $task->due_date->format('d M Y') }}
                @else
                    · No due date
                @endif
            </div>
        </li>
        @empty
        <li class="text-sm text-muted">No open tasks right now.</li>
        @endforelse
    </ul>
    @if($dashboardTasksHasMore ?? false)
    <div id="dashboardTasksActions" class="d-flex justify-content-center mt-3" style="gap:0.5rem">
        <button type="button" class="btn btn--outline btn--sm" id="seeMoreDashboardTasks">See More</button>
        <button type="button" class="btn btn--outline btn--sm" id="seeLessDashboardTasks" style="display:none">See Less</button>
    </div>
    @else
    <div id="dashboardTasksActions" class="d-flex justify-content-center mt-3" style="gap:0.5rem;display:none !important" aria-hidden="true"></div>
    @endif
</aside>
</div>

<div id="historyModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="width:600px;background:#fff;padding:25px;border-radius:12px;position:relative">
        <button type="button" class="icon-btn"
                style="position:absolute;top:15px;right:15px;font-size:24px;line-height:1;color:var(--gray-400);cursor:pointer;border:none;background:none"
                onclick="document.getElementById('historyModal').style.display='none'">&times;</button>
        <h3 id="historyTitle" style="margin-top:0; margin-bottom: 1.5rem; font-weight: 800;">Service History Details</h3>
        <div id="historyBody"></div>
        <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn--ghost btn--sm" onclick="document.getElementById('historyModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<div id="imagePreviewModal" class="modal" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.8);align-items:center;justify-content:center" onclick="this.style.display='none'">
    <div style="position:relative; max-width:90%; max-height:90%;">
        <button type="button" style="position:absolute; top:-40px; right:0; background:none; border:none; color:#fff; font-size:30px; cursor:pointer;">&times;</button>
        <img id="previewImage" src="" style="width:100%; height:auto; border-radius:8px; background:#fff;">
    </div>
</div>

<div class="modal-backdrop" id="recentActivityModal" role="dialog" aria-modal="true" aria-labelledby="recentActivityModalTitle">
    <div class="modal" style="width:min(720px,94vw)" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title" id="recentActivityModalTitle">Recent activity</div>
            <button type="button" class="modal-close" id="recentActivityModalClose" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" style="padding-top:0">
            <table class="table" id="recentActivityModalTable">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="recentActivityModalBody"></tbody>
            </table>
            <p class="text-sm text-muted text-center py-4" id="recentActivityModalEmpty" style="display:none;">No recent activity.</p>
        </div>
    </div>
</div>
@endsection
