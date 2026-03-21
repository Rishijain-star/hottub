<aside class="panel-sidebar">
    <div class="panel-sidebar__head">
        <div>
            <div class="panel-sidebar__title">Customer Panel</div>
            <div class="panel-sidebar__sub">Manage your ownership</div>
        </div>
    </div>
    <nav class="panel-nav">
        <a href="{{ route('customer.overview') }}" class="panel-nav-link {{ request()->routeIs('customer.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="{{ route('customer.service-history') }}" class="panel-nav-link {{ request()->routeIs('customer.service-history') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            Service History
        </a>
        <a href="{{ route('customer.hot-tub') }}" class="panel-nav-link {{ request()->routeIs('customer.hot-tub') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M7 10V6a5 5 0 0 1 10 0v4"/></svg>
            My Hot Tub
        </a>
        <a href="{{ route('customer.service-requests') }}" class="panel-nav-link {{ request()->routeIs('customer.service-requests') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Service Requests
        </a>
        <a href="{{ route('customer.request-history') }}" class="panel-nav-link {{ request()->routeIs('customer.request-history') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M3 20h4M7 4h10M12 4v16M7 8h10M7 12h10M7 16h10"/></svg>
            Request History
        </a>
        <a href="{{ route('customer.messages') }}" class="panel-nav-link {{ request()->routeIs('customer.messages') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
            Messages
        </a>
        <a href="{{ route('customer.profile') }}" class="panel-nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:.5rem">@csrf<button class="panel-nav-link" style="width:100%;border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:.75rem"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg> Logout</button></form>
    </nav>
</aside>
