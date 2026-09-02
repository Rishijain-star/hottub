<aside class="panel-sidebar" id="dealerSidebar">
    @php
        $hasUnreadAvailableLeadsDot = \App\Models\Notification::where('user_id', auth()->id())
            ->where('type', 'available_leads')
            ->where('read', false)
            ->exists();
        $hasUnreadDealerAcademyDot = \App\Models\Notification::where('user_id', auth()->id())
            ->where('type', 'dealer_academy')
            ->where('read', false)
            ->exists();
    @endphp

    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">{{ __('panel.dealer_title') }}</div>
            <div class="panel-sidebar__sub">{{ __('panel.dealer_sub') }}</div>
        </div>
    </div>

    <nav class="panel-nav">

        <a href="{{ route('dealer.overview') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            {{ __('panel.nav.overview') }}
        </a>

        <a href="{{ route('dealer.quotes') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.quotes*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 12.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            {{ __('panel.nav.available_leads') }}
            @if($hasUnreadAvailableLeadsDot)
                <span style="width:10px;height:10px;border-radius:999px;background:#ef4444;display:inline-block;margin-left:auto;"></span>
            @endif
        </a>

        <a href="{{ route('dealer.leads.index') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.leads*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            {{ __('panel.nav.my_leads') }}
        </a>

        <a href="{{ route('dealer.customers.index') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.customers*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            {{ __('panel.nav.my_customers') }}
        </a>

        <a href="{{ route('dealer.maintenance-packages') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.maintenance-packages*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            {{ __('panel.nav.maintenance_packages') }}
        </a>

        <a href="{{ route('dealer.service-history') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.service-history*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            {{ __('panel.nav.service_history') }}
        </a>

        <a href="{{ route('dealer.service-requests') }}"
       class="panel-nav-link {{ request()->routeIs('dealer.service-requests*') ? 'active' : '' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        {{ __('panel.nav.service_requests') }}
        @php
            $pendingServices = \App\Models\ServiceRequest::where('dealer_id', auth()->id())->where('status', 'pending')->count();
        @endphp
        @if($pendingServices > 0)
            <span class="notification-badge">{{ $pendingServices }}</span>
        @endif
    </a>

    <a href="{{ route('dealer.service-management') }}"
       class="panel-nav-link {{ request()->routeIs('dealer.service-management*') ? 'active' : '' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        {{ __('panel.nav.service_management') }}
    </a>

    <a href="{{ route('dealer.package-requests') }}"
       class="panel-nav-link {{ request()->routeIs('dealer.package-requests*') ? 'active' : '' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        {{ __('panel.nav.follow_up_requests') }}
        @php
            $pendingPackages = \App\Models\PackageRequest::where('dealer_id', auth()->id())->where('status', 'pending')->count();
        @endphp
        @if($pendingPackages > 0)
            <span class="notification-badge">{{ $pendingPackages }}</span>
        @endif
    </a>

    <a href="{{ route('dealer.academy.index') }}"
       class="panel-nav-link {{ request()->routeIs('dealer.academy*') ? 'active' : '' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
        </svg>
        {{ __('panel.nav.dealer_academy') }}
        @if($hasUnreadDealerAcademyDot)
            <span style="width:10px;height:10px;border-radius:999px;background:#ef4444;display:inline-block;margin-left:auto;"></span>
        @endif
    </a>

        <a href="{{ route('dealer.credits') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.credits*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            {{ __('panel.nav.credits') }}
        </a>

        <a href="{{ route('dealer.profile') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.profile*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            {{ __('panel.nav.profile') }}
        </a>

        <a href="{{ route('dealer.messages') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.messages*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-14.7 8.38 8.38 0 0 1 3.8.9L21 3z"/>
            </svg>
            {{ __('panel.nav.messages') }}
            @php
                $unreadDealerMessages = \App\Models\Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
            @endphp
            <span id="messages-nav-unread-badge" class="notification-badge" style="{{ $unreadDealerMessages > 0 ? 'margin-left:auto;' : 'display:none;' }}">{{ $unreadDealerMessages > 0 ? $unreadDealerMessages : '' }}</span>
        </a>

        <a href="{{ route('dealer.payments') }}"
           class="panel-nav-link {{ request()->routeIs('dealer.payments*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="4" width="20" height="14" rx="2"/>
                <path d="M2 10h20"/>
                <path d="M6 14h2"/>
            </svg>
            {{ __('panel.nav.accounting') }}
        </a>

    </nav>

</aside>