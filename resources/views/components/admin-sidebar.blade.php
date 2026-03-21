<aside class="panel-sidebar" id="adminSidebar">

    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">Admin Panel</div>
            <div class="panel-sidebar__sub">Manage your platform</div>
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
            Overview
        </a>

        <a href="{{ route('admin.hot-tubs.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.hot-tubs.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9a9 9 0 0 1 18 0v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/>
                <path d="M12 16v4M8 20h8"/>
            </svg>
            Hot Tubs
        </a>

        <a href="{{ route('admin.service-management') }}"
           class="panel-nav-link {{ request()->routeIs('admin.service-management*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            Service Management
        </a>

        <a href="{{ route('admin.outdoor-products.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.outdoor-products.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            Outdoor Products
        </a>

        <a href="{{ route('admin.brands.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Brands
        </a>

        <a href="{{ route('admin.services') }}"
           class="panel-nav-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            Services
        </a>

        <a href="{{ route('admin.parts') }}"
           class="panel-nav-link {{ request()->routeIs('admin.parts*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
            </svg>
            Parts
        </a>

        <a href="{{ route('admin.featured') }}"
           class="panel-nav-link {{ request()->routeIs('admin.featured*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            Featured Content
        </a>

        <a href="{{ route('admin.dealers.index') }}"
           class="panel-nav-link {{ request()->routeIs('admin.dealers.*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Dealers
            @if(isset($pendingDealers) && $pendingDealers > 0)
                <span class="panel-nav-badge">{{ $pendingDealers }}</span>
            @endif
        </a>

        <a href="{{ route('admin.manufacturers') }}"
           class="panel-nav-link {{ request()->routeIs('admin.manufacturers*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
            Manufacturers
        </a>

        <a href="{{ route('admin.leads') }}"
           class="panel-nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Leads
        </a>

        <a href="{{ route('admin.payments') }}"
           class="panel-nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            Payments
        </a>

        <a href="{{ route('admin.pricing-processor') }}"
           class="panel-nav-link {{ request()->routeIs('admin.pricing-processor*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
            Pricing Processor
        </a>

        <a href="{{ route('admin.pricing') }}"
           class="panel-nav-link {{ request()->routeIs('admin.pricing*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Price
        </a>

    </nav>

</aside>