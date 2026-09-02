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
        <span>{!! __('nav.promo', ['amount' => '<b>'.$promoSavingsFormatted.'</b>']) !!}</span>
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
                    <span class="logo-tagline">{{ __('nav.tagline') }}</span>
                </div>
            </a>
        </div>

        {{-- ── DESKTOP NAV ── --}}
        <nav class="navbar-center" aria-label="Main navigation">
            <ul class="menu-list">
                <li><a href="{{ url('/hot-tubs') }}"    class="menu-link {{ request()->is('hot-tubs*')    ? 'active' : '' }}">{{ __('nav.hot_tubs') }}</a></li>
                <li><a href="{{ url('/swim-spas') }}"   class="menu-link {{ request()->is('swim-spas*')   ? 'active' : '' }}">{{ __('nav.swim_spas') }}</a></li>
                <li><a href="{{ url('/outdoor-products') }}" class="menu-link {{ request()->is('outdoor-products*') ? 'active' : '' }}">{{ __('nav.outdoor_products') }}</a></li>
                <li><a href="{{ url('/services') }}"    class="menu-link {{ request()->is('services*')    ? 'active' : '' }}">{{ __('nav.services') }}</a></li>
                <li><a href="{{ url('/parts') }}"       class="menu-link {{ request()->is('parts*')       ? 'active' : '' }}">{{ __('nav.parts') }}</a></li>
                <li><a href="{{ url('/brands') }}"      class="menu-link {{ request()->is('brands*')      ? 'active' : '' }}">{{ __('nav.brands') }}</a></li>
                <li><a href="{{ url('/find-dealer') }}" class="menu-link {{ request()->is('find-dealer*') ? 'active' : '' }}">{{ __('nav.find_dealer') }}</a></li>
                <li class="nav-dropdown {{ request()->is('care-guide*', 'faq*') ? 'is-active' : '' }}">
                    <button type="button" class="menu-link nav-dropdown__toggle {{ request()->is('care-guide*', 'faq*') ? 'active' : '' }}" aria-expanded="false" aria-haspopup="true">
                        {{ __('nav.help') }}
                        <svg class="nav-dropdown__icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <ul class="nav-dropdown__menu" role="menu">
                        <li role="none"><a href="{{ url('/care-guide') }}" class="nav-dropdown__link {{ request()->is('care-guide*') ? 'active' : '' }}" role="menuitem">{{ __('nav.care_guide') }}</a></li>
                        <li role="none"><a href="{{ url('/faq') }}" class="nav-dropdown__link {{ request()->is('faq*') ? 'active' : '' }}" role="menuitem">{{ __('nav.faq') }}</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        {{-- ── RIGHT ACTIONS ── --}}
        <div class="navbar-right">

            @php $showCurrencyPicker = request()->is('dealer*', 'manufacturer*'); @endphp
            <x-locale-selector :show-currency="$showCurrencyPicker" />

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ url('/admin') }}" class="btn-login {{ request()->is('admin*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        {{ __('nav.admin') }}
                    </a>
                @elseif(auth()->user()->role === 'dealer')
                    <a href="{{ url('/dealer') }}" class="btn-login {{ request()->is('dealer*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        {{ __('nav.dashboard') }}
                    </a>
                @elseif(auth()->user()->role === 'manufacturer')
                    <a href="{{ url('/manufacturer') }}" class="btn-login {{ request()->is('manufacturer*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        {{ __('nav.dashboard') }}
                    </a>
                @else
                    @if(auth()->user()->phone_verified_at)
                    @php
                        $unreadCount = \App\Models\Notification::where('user_id', auth()->id())->where('read', false)->count();
                    @endphp
                    <a href="{{ url('/customer') }}" class="btn-login {{ request()->is('customer*') ? 'active' : '' }}" style="position:relative;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        {{ __('nav.customer_panel') }}
                        @if($unreadCount > 0)
                            <span style="position:absolute;top:-4px;right:-6px;background:#ef4444;color:#fff;font-size:0.65rem;font-weight:700;border-radius:999px;min-width:16px;height:16px;display:flex;align-items:center;justify-content:center;padding:0 4px;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    @endif
                @endif
                <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-login">{{ __('nav.logout') }}</button>
                </form>
            @else
                <a href="{{ url('/login') }}"    class="btn-login">{{ __('nav.login') }}</a>
                <a href="{{ url('/register') }}" class="btn-register">{{ __('nav.register') }}</a>
            @endauth

            <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>

    {{-- ── MOBILE MENU ── --}}
    <nav class="mobile-menu" id="mobileMenu" aria-label="Mobile navigation">
        <ul class="mobile-menu-list">
            <li style="padding:0.5rem 1rem;"><x-locale-selector :show-currency="$showCurrencyPicker" /></li>
            <li class="mobile-divider"></li>
            <li><a href="{{ url('/hot-tubs') }}"    class="mobile-menu-link">{{ __('nav.hot_tubs') }}</a></li>
            <li><a href="{{ url('/swim-spas') }}"   class="mobile-menu-link">{{ __('nav.swim_spas') }}</a></li>
            <li><a href="{{ url('/outdoor-products') }}" class="mobile-menu-link">{{ __('nav.outdoor_products') }}</a></li>
            <li><a href="{{ url('/services') }}"    class="mobile-menu-link">{{ __('nav.services') }}</a></li>
            <li><a href="{{ url('/parts') }}"       class="mobile-menu-link">{{ __('nav.parts') }}</a></li>
            <li><a href="{{ url('/brands') }}"      class="mobile-menu-link">{{ __('nav.brands') }}</a></li>
            <li><a href="{{ url('/find-dealer') }}" class="mobile-menu-link">📍 {{ __('nav.find_dealer') }}</a></li>
            <li class="mobile-menu-group">
                <div class="mobile-menu-group__label">{{ __('nav.help') }}</div>
                <a href="{{ url('/care-guide') }}" class="mobile-menu-link mobile-menu-link--sub">{{ __('nav.care_guide') }}</a>
                <a href="{{ url('/faq') }}" class="mobile-menu-link mobile-menu-link--sub">{{ __('nav.faq') }}</a>
            </li>
            <li class="mobile-divider"></li>
            @auth
                @if(auth()->user()->role === 'admin')
                    <li><a href="{{ url('/admin') }}" class="mobile-menu-link">⚙️ {{ __('nav.admin') }}</a></li>
                @elseif(auth()->user()->role === 'dealer')
                    <li><a href="{{ url('/dealer') }}" class="mobile-menu-link">📊 {{ __('nav.dashboard') }}</a></li>
                @elseif(auth()->user()->role === 'manufacturer')
                    <li><a href="{{ url('/manufacturer') }}" class="mobile-menu-link">🏭 {{ __('nav.dashboard') }}</a></li>
                @else
                    @if(auth()->user()->phone_verified_at)
                    <li><a href="{{ url('/customer') }}" class="mobile-menu-link">👤 {{ __('nav.customer_panel') }}</a></li>
                    @endif
                @endif
                <li>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="mobile-menu-link" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:0;">
                            🚪 {{ __('nav.logout') }}
                        </button>
                    </form>
                </li>
            @else
                <li><a href="{{ url('/login') }}"    class="mobile-menu-link">{{ __('nav.login') }}</a></li>
                <li><a href="{{ url('/register') }}" class="mobile-menu-link mobile-cta">{{ __('nav.register') }}</a></li>
            @endauth
        </ul>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function closeAllLocalePickers() {
        document.querySelectorAll('.locale-picker.is-open').forEach(function (picker) {
            picker.classList.remove('is-open');
            var trigger = picker.querySelector('.locale-picker__trigger');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.locale-picker__trigger');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            var picker = trigger.closest('.locale-picker');
            if (!picker) return;
            var wasOpen = picker.classList.contains('is-open');
            closeAllLocalePickers();
            if (!wasOpen) {
                picker.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
            return;
        }
        if (!e.target.closest('.locale-picker__menu')) {
            closeAllLocalePickers();
        }
    });

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

    document.querySelectorAll('.nav-dropdown').forEach(function (dropdown) {
        var toggle = dropdown.querySelector('.nav-dropdown__toggle');
        if (!toggle) return;
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('is-open');
            document.querySelectorAll('.nav-dropdown.is-open').forEach(function (d) {
                d.classList.remove('is-open');
                d.querySelector('.nav-dropdown__toggle')?.setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                dropdown.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });
    document.addEventListener('click', function () {
        document.querySelectorAll('.nav-dropdown.is-open').forEach(function (d) {
            d.classList.remove('is-open');
            d.querySelector('.nav-dropdown__toggle')?.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>
