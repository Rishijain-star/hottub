<aside class="panel-sidebar">
    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">{{ __('panel.customer_title') }}</div>
            <div class="panel-sidebar__sub">{{ __('panel.customer_sub') }}</div>
        </div>
    </div>
    <nav class="panel-nav">
        <a href="{{ route('customer.overview') }}" class="panel-nav-link {{ request()->routeIs('customer.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            {{ __('panel.nav.dashboard') }}
        </a>
        <a href="{{ route('customer.hot-tub') }}" class="panel-nav-link {{ request()->routeIs('customer.hot-tub') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M7 10V6a5 5 0 0 1 10 0v4"/></svg>
            {{ __('panel.nav.my_hot_tub') }}
        </a>
        <a href="{{ route('customer.service-requests') }}" class="panel-nav-link {{ request()->routeIs('customer.service-requests') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            {{ __('panel.nav.service_requests') }}
            @php
                $pendingServiceRequests = \App\Models\ServiceRequest::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->count();
            @endphp
            @if($pendingServiceRequests > 0)
                <span style="background:#ef4444;color:#fff;min-width:18px;height:18px;padding:0 5px;border-radius:10px;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-left:auto;">{{ $pendingServiceRequests }}</span>
            @endif
        </a>
        <a href="{{ route('customer.request-history') }}" class="panel-nav-link {{ request()->routeIs('customer.request-history') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M3 20h4M7 4h10M12 4v16M7 8h10M7 12h10M7 16h10"/></svg>
            {{ __('panel.nav.request_history') }}
        </a>
        <a href="{{ route('customer.messages') }}" class="panel-nav-link {{ request()->routeIs('customer.messages') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
            {{ __('panel.nav.messages') }}
            @php
                $unreadCustomerMessages = \App\Models\Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
            @endphp
            <span id="messages-nav-unread-badge" class="notification-badge" style="{{ $unreadCustomerMessages > 0 ? 'margin-left:auto;' : 'display:none;' }}">{{ $unreadCustomerMessages > 0 ? $unreadCustomerMessages : '' }}</span>
        </a>
        <a href="{{ route('customer.profile') }}" class="panel-nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ __('panel.nav.profile_settings') }}
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:.5rem">@csrf<button class="panel-nav-link" style="width:100%;border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:.75rem"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg> {{ __('panel.nav.logout') }}</button></form>
    </nav>
</aside>
