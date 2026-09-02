<aside class="panel-sidebar" id="adminSidebar">

    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">{{ __('panel.admin_title') }}</div>
            <div class="panel-sidebar__sub">{{ __('panel.admin_sub') }}</div>
        </div>
    </div>

    <nav class="panel-nav">

        <a href="{{ route('admin.overview') }}"
           class="panel-nav-link {{ request()->routeIs('admin.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            {{ __('panel.admin.nav.overview') }}
        </a>

        <a href="{{ route('admin.analytics.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-8"/>
            </svg>
            {{ __('panel.admin.nav.analytics') }}
        </a>

        <a href="{{ route('admin.security') }}"
           class="panel-nav-link {{ request()->routeIs('admin.security*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            {{ __('panel.admin.nav.security') }}
        </a>

        @php
            $pendingPartnersSidebar = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status')
                ? \App\Models\User::whereIn('role', ['dealer', 'manufacturer'])->where('status', 'pending')->count()
                : 0;
        @endphp
        <a href="{{ route('admin.dealers.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.dealers.*') || request()->routeIs('admin.manufacturers*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            {{ __('panel.admin.nav.partner_signups') }}
            @if($pendingPartnersSidebar > 0)
                <span style="background:#ef4444;color:#fff;min-width:18px;height:18px;padding:0 5px;border-radius:10px;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-left:auto;">{{ $pendingPartnersSidebar }}</span>
            @endif
        </a>

        <a href="{{ route('admin.settings') }}"
           class="panel-nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
            </svg>
            {{ __('panel.admin.nav.homepage_images') }}
        </a>

        <a href="{{ route('admin.support-requests') }}"
           class="panel-nav-link {{ request()->routeIs('admin.support-requests*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            {{ __('panel.admin.nav.support_requests') }}
            @php
                $hasSupportStatusCol = \Illuminate\Support\Facades\Schema::hasColumn('messages', 'support_status');
                $pendingSupportRequests = \App\Models\Message::where('receiver_id', 1)
                    ->when($hasSupportStatusCol, function ($q) {
                        $q->whereNotNull('support_status')->where('support_status', 'pending');
                    })
                    ->count();
            @endphp
            @if($pendingSupportRequests > 0)
                <span style="background:#ef4444;color:#fff;min-width:18px;height:18px;padding:0 5px;border-radius:10px;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-left:auto;">{{ $pendingSupportRequests }}</span>
            @endif
        </a>

        <a href="{{ route('admin.hot-tubs.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.hot-tubs.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9a9 9 0 0 1 18 0v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/>
                <path d="M12 16v4M8 20h8"/>
            </svg>
            {{ __('panel.admin.nav.hot_tubs_swim_spas') }}
        </a>

        <a href="{{ route('admin.service-management') }}"
           class="panel-nav-link {{ request()->routeIs('admin.service-management*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            {{ __('panel.admin.nav.service_management') }}
        </a>

        <a href="{{ route('admin.outdoor-products.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.outdoor-products.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            {{ __('panel.admin.nav.outdoor_products') }}
        </a>

        <a href="{{ route('admin.brands.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            {{ __('panel.admin.nav.brands') }}
        </a>

        <a href="{{ route('admin.services.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            {{ __('panel.admin.nav.services') }}
        </a>

        <a href="{{ route('admin.parts') }}"
           class="panel-nav-link {{ request()->routeIs('admin.parts*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
            </svg>
            {{ __('panel.admin.nav.parts') }}
        </a>

        @if(auth()->user()?->isFullAdmin())
        <a href="{{ route('admin.plans') }}"
           class="panel-nav-link {{ request()->routeIs('admin.plans*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            {{ __('panel.admin.nav.credit_plans') }}
        </a>
        @endif

        <a href="{{ route('admin.featured') }}"
           class="panel-nav-link {{ request()->routeIs('admin.featured*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            {{ __('panel.admin.nav.featured_content') }}
        </a>

        <a href="{{ route('admin.dealer-academy.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.dealer-academy.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
            </svg>
            {{ __('panel.admin.nav.dealer_academy') }}
        </a>

        @if(auth()->user()?->isFullAdmin())
        <a href="{{ route('admin.dealers.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.dealers.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            {{ __('panel.admin.nav.dealers') }}
            @if(isset($pendingDealers) && $pendingDealers > 0)
                <span class="panel-nav-badge">{{ $pendingDealers }}</span>
            @endif
        </a>
        @endif

        <a href="{{ route('admin.users.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.update') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            {{ __('panel.admin.nav.users') }}
        </a>
        @if(auth()->user()?->isFullAdmin())
        <a href="{{ route('admin.users.create') }}"
           class="panel-nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}"
           style="padding-left:2rem;font-size:0.9rem">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('panel.admin.nav.add_sub_admin') }}
        </a>
        @endif

        @if(auth()->user()?->isFullAdmin())
        <a href="{{ route('admin.manufacturers') }}"
           class="panel-nav-link {{ request()->routeIs('admin.manufacturers*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
            {{ __('panel.admin.nav.manufacturers') }}
        </a>
        @endif

        <a href="{{ route('admin.leads') }}"
           class="panel-nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            {{ __('panel.admin.nav.leads') }}
        </a>

        @if(auth()->user()?->isFullAdmin())
        <a href="{{ route('admin.payments') }}"
           class="panel-nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            {{ __('panel.admin.nav.payments') }}
        </a>

        <a href="{{ route('admin.pricing-processor') }}"
           class="panel-nav-link {{ request()->routeIs('admin.pricing-processor*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
            {{ __('panel.admin.nav.pricing_processor') }}
        </a>

        <a href="{{ route('admin.pricing') }}"
           class="panel-nav-link {{ request()->routeIs('admin.pricing*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            {{ __('panel.admin.nav.price') }}
        </a>
        @endif

    </nav>

</aside>