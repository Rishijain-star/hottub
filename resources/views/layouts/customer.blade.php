<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Customer Panel – Hot Tub Buyer')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">

    @yield('styles')
    </head>
<body class="panel-body">
    @include('layouts.header')
    <div class="panel-wrapper">
        @include('components.customer-sidebar')
        <div class="panel-main">
            <div class="panel-content">
                @yield('content')
            </div>
        </div>
    </div>
    @include('layouts.footer')
    @yield('scripts')
    <script>
        document.querySelectorAll('.panel-nav-link').forEach(link => {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>
