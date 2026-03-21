<aside class="panel-sidebar" id="manufacturerSidebar">

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
            <div style="position: relative;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-14.7 8.38 8.38 0 0 1 3.8.9L21 3z"/>
                </svg>
                <div id="msg-badge" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: #fff; width: 14px; height: 14px; border-radius: 50%; font-size: 0.6rem; display: none; align-items: center; justify-content: center; font-weight: 800;"></div>
            </div>
            Messages
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

