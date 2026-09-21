<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title') &mdash; {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS files -->
    <link href="{{asset('css/tabler.min.css?1738096682')}}" rel="stylesheet" />
    <link href="{{asset('css/custom-theme.css')}}?v={{ time() }}" rel="stylesheet" />
    <link href="{{asset('css/cio-saham.css')}}?v={{ time() }}" rel="stylesheet" />
</head>
<body>
    <div class="auth-page-wrapper">
        <div class="auth-card">
            <div class="auth-logo-wrap">
                <a href="{{route('login')}}">
                    <img src="{{asset('img/logo.jpg')}}" alt="{{ config('app.name') }}" style="border-radius: 8px;" />
                </a>
            </div>

            @if (session()->has('success'))
                @include('components.alert.success')
            @endif

            @if (session()->has('error'))
                @include('components.alert.danger')
            @endif

            @if (session()->has('warning'))
                @include('components.alert.warning')
            @endif

            @yield('content')

            <div class="text-center mt-4">
                <p class="text-muted small mb-0">
                    &copy; {{ date('Y') }} <strong class="text-dark">{{ config('app.name') }}</strong>. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabler Core JS -->
    <script src="{{asset('js/tabler.min.js?1738096682')}}" defer></script>
</body>
</html>
