<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-google-translate="{{ $googleTranslateLang ?? '' }}">
<head>
    @php
        $globalCssPath = public_path('css/global.css');
        $globalCssVersion = file_exists($globalCssPath) ? filemtime($globalCssPath) : null;
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Compare hot tubs, swim spas, prices and expert reviews at Hot Tub Buyer.')">

    <title>@yield('title', 'Hot Tub Buyer - Expert Reviews & Guides')</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Google Font: DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    @stack('head')

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/global.css') }}{{ $globalCssVersion ? ('?v=' . $globalCssVersion) : '' }}">

    @yield('styles')

    @include('components.flag-icons-css')

    @include('components.google-translate-head')

    @if(config('analytics.measurement_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('analytics.measurement_id') }}');
    </script>
    @endif

    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "x087zurmb1");
    </script>
</head>
<body>

    {{-- ── Header ── --}}
    @include('layouts.header')

    {{-- ── Page Content ── --}}
    <main>
        @yield('content')
    </main>

    {{-- ── Footer ── --}}
    @include('layouts.footer')

    @include('components.enquiry-modal')
    @yield('scripts')
    @stack('scripts')
    <x-google-translate />
    @include('components.geo-locator')

    @php
        $toastMessage = session('error');
        if (! $toastMessage && isset($errors) && $errors->any()) {
            $toastMessage = $errors->first();
        }
    @endphp
    @if(session('show_toast') && $toastMessage)
    <div id="appToast" role="alert" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#111827;color:#fff;padding:12px 20px;border-radius:10px;z-index:99999;max-width:min(92vw,420px);box-shadow:0 10px 40px rgba(0,0,0,.25);font-size:0.92rem;text-align:center;">
        {{ $toastMessage }}
    </div>
    <script>setTimeout(function(){var t=document.getElementById('appToast');if(t)t.remove();},7000);</script>
    @endif
    <script>
        function closePromoBar() {
            const bar = document.getElementById('topPromoBar');
            if (bar) {
                bar.style.display = 'none';
                // Optional: Save to localStorage so it stays hidden for this session
                localStorage.setItem('promo_bar_hidden', 'true');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('promo_bar_hidden') === 'true') {
                const bar = document.getElementById('topPromoBar');
                if (bar) bar.style.display = 'none';
            }
        });

        (function () {
            const runImageHints = function () {
                const allImages = Array.from(document.querySelectorAll('img'));
                if (!allImages.length) return;

                const skipSelectors = [
                    '.header img',
                    '.navbar img',
                    '.site-logo img',
                    'img.site-logo',
                    '[data-no-lazy]'
                ];

                const shouldSkipLazy = function (img, index) {
                    if (index < 2) return true;
                    return skipSelectors.some(function (selector) {
                        return img.matches(selector) || img.closest(selector);
                    });
                };

                allImages.forEach(function (img, index) {
                    if (!img.hasAttribute('decoding')) {
                        img.setAttribute('decoding', 'async');
                    }

                    if (shouldSkipLazy(img, index)) {
                        if (!img.hasAttribute('loading')) {
                            img.setAttribute('loading', 'eager');
                        }
                        if (!img.hasAttribute('fetchpriority')) {
                            img.setAttribute('fetchpriority', 'high');
                        }
                        return;
                    }

                    if (!img.hasAttribute('loading')) {
                        img.setAttribute('loading', 'lazy');
                    }
                    if (!img.hasAttribute('fetchpriority')) {
                        img.setAttribute('fetchpriority', 'low');
                    }
                });
            };

            if ('requestIdleCallback' in window) {
                requestIdleCallback(runImageHints, { timeout: 1200 });
            } else {
                setTimeout(runImageHints, 0);
            }

            const prefetched = new Set();
            const shouldPrefetch = function (url) {
                try {
                    const u = new URL(url, window.location.origin);
                    if (u.origin !== window.location.origin) return false;
                    if (u.pathname === window.location.pathname && u.search === window.location.search) return false;
                    if (u.hash && u.pathname === window.location.pathname) return false;
                    return !prefetched.has(u.href);
                } catch (e) {
                    return false;
                }
            };

            const prefetchHref = function (href) {
                if (!shouldPrefetch(href)) return;
                const u = new URL(href, window.location.origin);
                prefetched.add(u.href);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = u.href;
                link.as = 'document';
                document.head.appendChild(link);
            };

            document.addEventListener('mouseover', function (event) {
                const a = event.target.closest('a[href]');
                if (a) prefetchHref(a.href);
            }, { passive: true });

            document.addEventListener('touchstart', function (event) {
                const a = event.target.closest('a[href]');
                if (a) prefetchHref(a.href);
            }, { passive: true });

            window.addEventListener('load', function () {
                const lazyScriptLoader = function () {
                    const thirdParty = document.createElement('script');
                    thirdParty.src = 'https://www.noupe.com/embed/019de29e9f2074e19deb10f33c38b5f7a472.js';
                    thirdParty.async = true;
                    document.body.appendChild(thirdParty);
                };

                if ('requestIdleCallback' in window) {
                    requestIdleCallback(lazyScriptLoader, { timeout: 5000 });
                } else {
                    setTimeout(lazyScriptLoader, 2000);
                }
            });
        })();
    </script>
    <script>
    (function () {
        function uuid() {
            if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        function setCookie(name, value, days) {
            var secure = location.protocol === 'https:' ? ';Secure' : '';
            document.cookie = name + '=' + encodeURIComponent(value) + ';path=/;max-age=' + (days * 86400) + ';SameSite=Lax' + secure;
        }
        function getCookie(name) {
            var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
            return match ? decodeURIComponent(match[1]) : '';
        }
        var pid = localStorage.getItem('htb_pid') || getCookie('htb_pid');
        if (!pid) { pid = uuid(); localStorage.setItem('htb_pid', pid); }
        setCookie('htb_pid', pid, 365);
        var hw = [
            (screen && screen.width ? screen.width : ''),
            (screen && screen.height ? screen.height : ''),
            (screen && screen.colorDepth ? screen.colorDepth : ''),
            String(new Date().getTimezoneOffset()),
            navigator.hardwareConcurrency || '',
            navigator.maxTouchPoints || 0,
            navigator.deviceMemory || ''
        ].join('|');
        setCookie('htb_hw', hw, 365);
    })();
    </script>
</body>
</html>
