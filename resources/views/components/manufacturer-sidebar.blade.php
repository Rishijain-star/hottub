<aside class="panel-sidebar" id="manufacturerSidebar">
    @php
        $hasUnreadAvailableLeadsDot = \App\Models\Notification::where('user_id', auth()->id())
            ->where('type', 'available_leads')
            ->where('read', false)
            ->exists();
    @endphp

    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">Manufacturer Panel</div>
            <div class="panel-sidebar__sub">Manage your brand</div>
        </div>
    </div>

    <nav class="panel-nav">

        <a href="{{ route('manufacturer.overview') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Overview
        </a>

        <a href="{{ route('manufacturer.quotes') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.quotes*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 12.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            Available Leads
            @if($hasUnreadAvailableLeadsDot)
                <span style="width:10px;height:10px;border-radius:999px;background:#ef4444;display:inline-block;margin-left:auto;"></span>
            @endif
        </a>

        <a href="{{ route('manufacturer.leads') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.leads*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            My Leads
        </a>

        <a href="{{ route('manufacturer.maintenance-packages') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.maintenance-packages*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Maintenance Packages
        </a>

        <a href="{{ route('manufacturer.service-history') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.service-history*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            Service History
        </a>

        <a href="{{ route('manufacturer.service-requests') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.service-requests*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Service Requests
            @php
                $pendingServices = \App\Models\ServiceRequest::where('dealer_id', auth()->id())->where('status', 'pending')->count();
            @endphp
            @if($pendingServices > 0)
                <span class="notification-badge">{{ $pendingServices }}</span>
            @endif
        </a>

        <a href="{{ route('manufacturer.package-requests') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.package-requests*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Follow-Up / Requests
            @php
                $pendingPackages = \App\Models\PackageRequest::where('dealer_id', auth()->id())->where('status', 'pending')->count();
            @endphp
            @if($pendingPackages > 0)
                <span class="notification-badge">{{ $pendingPackages }}</span>
            @endif
        </a>

        <a href="{{ route('manufacturer.credits') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.credits*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Credits
        </a>

        <a href="{{ route('manufacturer.profile') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.profile*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Profile
        </a>

        <a href="{{ route('manufacturer.messages') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.messages*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-14.7 8.38 8.38 0 0 1 3.8.9L21 3z"/>
            </svg>
            Messages
            @php
                $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
            @endphp
            @if($unreadMessages > 0)
                <span style="background:#ef4444;color:#fff;min-width:18px;height:18px;padding:0 5px;border-radius:10px;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-left:auto;">{{ $unreadMessages }}</span>
            @endif
        </a>

        <a href="{{ route('manufacturer.payments') }}"
           class="panel-nav-link {{ request()->routeIs('manufacturer.payments*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
            Payments
        </a>

    </nav>
</aside>

