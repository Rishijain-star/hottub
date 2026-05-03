<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $globalCssPath = public_path('css/global.css');
        $globalCssVersion = file_exists($globalCssPath) ? filemtime($globalCssPath) : null;
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Compare hot tubs, swim spas, prices and expert reviews at Hot Tub Buyer.')">

    <title>@yield('title', 'Hot Tub Buyer - Expert Reviews & Guides')</title>

    <!-- Google Font: DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/global.css') }}{{ $globalCssVersion ? ('?v=' . $globalCssVersion) : '' }}">

    @yield('styles')
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
                    thirdParty.src = 'https://www.noupe.com/embed/019dcfcf83227ff7b31939a74335802755bd.js';
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
</body>
</html>
