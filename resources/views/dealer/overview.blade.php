@extends('layouts.dealer')
@section('title', 'Overview – Dealer Panel')
@section('styles')
<style>.steps{display:flex;flex-direction:column;gap:.6rem}.step{display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;border:1px solid #e3edff;background:#f5f9ff;border-radius:var(--r-lg);font-weight:600;color:#1e3a8a}.step__num{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#2563eb;color:#fff;font-size:.8rem}.step__text{flex:1}</style>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        var recentActivityPage = 1;
        var recentActivityInitialItems = 5;
        var recentActivityTotalItems = 0;

        function loadRecentActivity(){
            $.ajax({
                url: "{{ route('dealer.overview') }}" + "?page=" + recentActivityPage,
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
        <div class="panel-stat-card__label">Converted Leads</div>
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
<div class="card">
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

<div class="card mt-4">
    <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Recent Requests from Customers
        @if($recentRequests->count() > 0)
            <span class="notification-badge">{{ $recentRequests->count() }}</span>
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
                    <td><a href="{{ route('dealer.service-requests') }}" class="btn btn--ghost btn--sm">View</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No recent requests.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
