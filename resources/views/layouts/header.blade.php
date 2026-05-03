{{-- ============================================================
     resources/views/layouts/header.blade.php
     HotTub Buyer — Main Navigation Header
     ============================================================ --}}

{{-- ── PROMOTIONAL TOP BAR ── --}}
@if(!auth()->check() || !in_array(auth()->user()->role, ['admin', 'dealer', 'manufacturer']))
<div class="top-promo-bar" id="topPromoBar">
    <div style="display: flex; align-items: center; gap: 8px;">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" stroke="currentColor" stroke-width="2.5" style="color: #ffd700;">
            <path d="M12 2L2 7l10 5 10-5-10-5ZM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>You can save up to <b>£3000</b> using Hot Tub Buyer.</span>
    </div>
</div>
@endif

<header class="navbar">
    <div class="navbar-wrapper">

        {{-- ── LOGO ── --}}
        <div class="navbar-left">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="logo-svg">
                        <path d="M12 2C12 2 5 9.5 5 14a7 7 0 0014 0C19 9.5 12 2 12 2Z" fill="white"/>
                        <path d="M9 16c0 1.657 1.343 3 3 3" stroke="rgba(0,180,170,0.5)" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="logo-text-group">
                    <div class="logo-name">
                        <span class="logo-name-plain">Hot Tub</span>
                        <span class="logo-name-bold"> Buyer</span>
                    </div>
                    <span class="logo-tagline">Expert Reviews &amp; Guides</span>
                </div>
            </a>
        </div>

        {{-- ── DESKTOP NAV ── --}}
        <nav class="navbar-center" aria-label="Main navigation">
            <ul class="menu-list">
                <li><a href="{{ url('/hot-tubs') }}"    class="menu-link {{ request()->is('hot-tubs*')    ? 'active' : '' }}">Hot Tubs</a></li>
                <li><a href="{{ url('/swim-spas') }}"   class="menu-link {{ request()->is('swim-spas*')   ? 'active' : '' }}">Swim Spas</a></li>
                <li><a href="{{ url('/outdoor-products') }}" class="menu-link {{ request()->is('outdoor-products*') ? 'active' : '' }}">Outdoor Products</a></li>
                <li><a href="{{ url('/services') }}"    class="menu-link {{ request()->is('services*')    ? 'active' : '' }}">Services</a></li>
                <li><a href="{{ url('/parts') }}"       class="menu-link {{ request()->is('parts*')       ? 'active' : '' }}">Parts</a></li>
                <li><a href="{{ url('/brands') }}"      class="menu-link {{ request()->is('brands*')      ? 'active' : '' }}">Brands</a></li>
                <li><a href="{{ url('/find-dealer') }}" class="menu-link {{ request()->is('find-dealer*') ? 'active' : '' }}">Find Dealer</a></li>
                <li><a href="{{ url('/care-guide') }}"  class="menu-link {{ request()->is('care-guide*')  ? 'active' : '' }}">Care Guide</a></li>
                <li><a href="{{ url('/faq') }}"         class="menu-link {{ request()->is('faq*')         ? 'active' : '' }}">FAQ</a></li>
            </ul>
        </nav>

        {{-- ── RIGHT ACTIONS ── --}}
        <div class="navbar-right">

            @auth
                {{-- ── Logged In: show role-based panel link + logout ── --}}

                @if(auth()->user()->role === 'admin')
                    <a href="{{ url('/admin') }}" class="btn-login {{ request()->is('admin*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        Admin
                    </a>
                @elseif(auth()->user()->role === 'dealer')
                    <a href="{{ url('/dealer') }}" class="btn-login {{ request()->is('dealer*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        Dashboard
                    </a>
                @elseif(auth()->user()->role === 'manufacturer')
                    <a href="{{ url('/manufacturer') }}" class="btn-login {{ request()->is('manufacturer*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        Dashboard
                    </a>
                @else
                    {{-- ── Customer Notification Icon ── --}}
                    @php
                        $unreadCount = \App\Models\Notification::where('user_id', auth()->id())->where('read', false)->count();
                    @endphp
                    <a href="{{ url('/customer') }}" class="btn-login" style="padding: 0.5rem; position: relative; margin-right: 0.5rem;" title="Notifications">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        @if($unreadCount > 0)
                            <span style="position: absolute; top: 2px; right: 2px; background: #ef4444; color: white; font-size: 10px; font-weight: 800; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border: 2px solid white;">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ url('/customer') }}" class="btn-login {{ request()->is('customer*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        My Account
                    </a>
                @endif

                <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-register" style="border:none;cursor:pointer;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>

            @else
                {{-- ── Guest: show Login + Register ── --}}
                <a href="{{ url('/login') }}"    class="btn-login {{ request()->is('login')    ? 'active' : '' }}">Login</a>
                <a href="{{ url('/register') }}" class="btn-register">Register</a>
            @endauth

            {{-- Hamburger (mobile only) --}}
            <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>

    {{-- ── MOBILE MENU ── --}}
    <nav class="mobile-menu" id="mobileMenu" aria-label="Mobile navigation">
        <ul class="mobile-menu-list">
            <li><a href="{{ url('/hot-tubs') }}"    class="mobile-menu-link">Hot Tubs</a></li>
            <li><a href="{{ url('/swim-spas') }}"   class="mobile-menu-link">Swim Spas</a></li>
            <li><a href="{{ url('/outdoor-products') }}" class="mobile-menu-link">Outdoor Products</a></li>
            <li><a href="{{ url('/services') }}"    class="mobile-menu-link">Services</a></li>
            <li><a href="{{ url('/parts') }}"       class="mobile-menu-link">Parts</a></li>
            <li><a href="{{ url('/brands') }}"      class="mobile-menu-link">Brands</a></li>
            <li><a href="{{ url('/find-dealer') }}" class="mobile-menu-link">📍 Find Dealer</a></li>
            <li><a href="{{ url('/care-guide') }}"  class="mobile-menu-link">Care Guide</a></li>
            <li><a href="{{ url('/faq') }}"         class="mobile-menu-link">FAQ</a></li>
            <li class="mobile-divider"></li>
            @auth
                @if(auth()->user()->role === 'admin')
                    <li><a href="{{ url('/admin') }}" class="mobile-menu-link">⚙️ Admin Panel</a></li>
                @elseif(auth()->user()->role === 'dealer')
                    <li><a href="{{ url('/dealer') }}" class="mobile-menu-link">📊 Dealer Panel</a></li>
                @elseif(auth()->user()->role === 'manufacturer')
                    <li><a href="{{ url('/manufacturer') }}" class="mobile-menu-link">🏭 Manufacturer Panel</a></li>
                @else
                    <li><a href="{{ url('/customer') }}" class="mobile-menu-link">👤 Customer Panel</a></li>
                @endif
                <li>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="mobile-menu-link" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:0;">
                            🚪 Logout
                        </button>
                    </form>
                </li>
            @else
                <li><a href="{{ url('/login') }}"    class="mobile-menu-link">Login</a></li>
                <li><a href="{{ url('/register') }}" class="mobile-menu-link mobile-cta">Register</a></li>
            @endauth
        </ul>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hamburger  = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    if (!hamburger || !mobileMenu) return;
    hamburger.addEventListener('click', function () {
        const open = mobileMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
        hamburger.setAttribute('aria-expanded', open);
    });
    mobileMenu.querySelectorAll('.mobile-menu-link').forEach(link => {
        link.addEventListener('click', closeMenu);
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.navbar')) closeMenu();
    });
    function closeMenu() {
        mobileMenu.classList.remove('active');
        hamburger.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
    }
});
</script>
