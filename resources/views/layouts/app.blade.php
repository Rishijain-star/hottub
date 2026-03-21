<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Compare hot tubs, swim spas, prices and expert reviews at Hot Tub Buyer.')">

    <title>@yield('title', 'Hot Tub Buyer - Expert Reviews & Guides')</title>

    <!-- Google Font: DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

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

        (function(){
            document.querySelectorAll('img:not([loading])').forEach(function(img){
                img.setAttribute('loading','lazy');
                img.setAttribute('decoding','async');
            });
        })();
    </script>
    <script src="https://www.noupe.com/embed/019c894cb6327795ab1911a42f649277b329.js"></script>
</body>
</html>
