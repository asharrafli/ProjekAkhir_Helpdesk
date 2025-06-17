<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles

    <style>
        .sidebar{
            height: 100vh;
            position: fixed;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s;
            padding: 20px;
            border-right: 1px solid #E5E7EB;
        }
        .left-sidebar{
            left: 0;
            width: 250px;
            background-color: #FFFFFF;
            color: #000000;
        }
        .nav-link{
            color: #000000;
            display: flex;
            align-items: center;
            padding: 2rem 1.5rem;
            transition: all 0.3s;
        }
        .nav-link i{
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content{
            margin-left: 250px;
            margin-right: 300px;
            padding: 20px;
        }
        .main-content.no-sidebar{
            margin-left: 0;
            margin-right: 0;
        }
    </style>

</head>
<body class="bg-white">
    <div id="app">
        @php
        $isAuthPage = Request::is('login') || Request::is('register')
        || Request::is('password/*')|| Request::is('email/verify/*');
        @endphp

        @unless($isAuthPage)
        <div class="sidebar left-sidebar">
            <div class="px-4 py-6 d-flex justify-content-center">
                <img src="{{ asset('images/Logo Icon Soluxio.png') }}" style="height: 121px; width: 121px;" alt="">
            </div>
            <nav class="mt-6">
                <a href="{{ route('home') }}" class="nav-link {{ Request::routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </nav>
        </div>
        @endunless

        {{-- main Content --}}
        <div class="main-content {{ $isAuthPage ? 'no-sidebar' : '' }}">
            @if ($isAuthPage)
            <div class="auth-container">
                <div class="auth-card">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </div>
            @else
            {{ $slot ?? '' }}
            @yield('content')
                
            @endif
        </div>
    </div>
</body>
</html>
