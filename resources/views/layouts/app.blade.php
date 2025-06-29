<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ Auth::id() }}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.12.1/font/bootstrap-icons.min.css">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        .sidebar{
            height: 100vh;
            position: fixed;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s;
            padding: 20px;
            border-right: 1px solid #E5E7EB;
            z-index: 1000;
        }
        .left-sidebar{
            left: 0;
            width: 250px;
            background-color: #FFFFFF;
            color: #000000;
        }
        .right-sidebar{
            right: 0;
            width: 300px;
            background-color: #F8F9FA;
            color: #000000;
        }
        .nav-link{
            color: #000000;
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.25rem;
        }
        .nav-link:hover, .nav-link.active{
            background-color: #EBF8FF;
            color: #1D4ED8;
            text-decoration: none;
        }
        .nav-link i{
            color: inherit;
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
        
        .user-profile-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: move;
            transition: transform 0.2s;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .stats-card.sortable-ghost {
            opacity: 0.4;
        }
        
        .dropdown-menu {
            z-index: 1050;
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
        <!-- Left Sidebar -->
        <div class="sidebar left-sidebar">
            <div class="px-4 py-6 d-flex justify-content-center">
                <img src="{{ asset('images/Logo Icon Soluxio.png') }}" style="height: 80px; width: 80px;" alt="">
            </div>
            <nav class="mt-4">
                <a href="{{ route('home') }}" class="nav-link {{ Request::routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                @can('view-manager-dashboard')
                <a href="{{ route('manager.dashboard') }}" class="nav-link {{ Request::routeIs('manager.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Manager Dashboard
                </a>
                @endcan

                @can('view-tickets')
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-ticket-perforated"></i> Tickets
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('tickets.index') }}">All Tickets</a></li>
                        <li><a class="dropdown-item" href="{{ route('tickets.create') }}">Create Ticket</a></li>
                    </ul>
                </div>
                @endcan

                @can('view-users')
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
                @endcan

                @can('view-roles')
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ Request::routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
                @endcan

                @can('view-permissions')
                <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ Request::routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-key"></i> Permissions
                </a>
                @endcan

                @can('manage-categories')
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Categories
                </a>
                @endcan

                @can('manage-categories')
                <a href="{{ route('admin.subcategories.index') }}" class="nav-link {{ Request::routeIs('admin.subcategories.*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i> Subcategories
                </a>
                @endcan

                @can('view-activity-logs')
                <a href="{{ route('admin.activity-logs') }}" class="nav-link {{ Request::routeIs('admin.activity-logs') ? 'active' : '' }}">
                    <i class="bi bi-activity"></i> Activity Logs
                </a>
                @endcan

                <a href="{{ route('profile') }}" class="nav-link {{ Request::routeIs('profile') ? 'active' : '' }}">
                    <i class="bi bi-person"></i> Profile
                </a>
            </nav>
        </div>

        <!-- Right Sidebar -->
        <div class="sidebar right-sidebar">
            <!-- Notification Bell -->
            <div class="notification-section mb-4">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary position-relative w-100" type="button" id="notificationBell" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i> Notifications
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="unreadBadge" style="display: none;">
                            0
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <li><span class="dropdown-item-text text-muted">Loading notifications...</span></li>
                    </ul>
                </div>
            </div>

            <div class="user-profile-card">
                <div class="text-center mb-3">
                    <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" 
                         class="rounded-circle" width="60" height="60" alt="Avatar">
                </div>
                <h6 class="text-center mb-1">{{ Auth::user()->name }}</h6>
                <p class="text-center text-muted small">{{ Auth::user()->email }}</p>
                <div class="text-center">
                    @if(Auth::user()->isSuperAdmin())
                        <span class="badge bg-danger">Super Admin</span>
                    @elseif(Auth::user()->isAdmin())
                        <span class="badge bg-primary">Admin</span>
                    @else
                        <span class="badge bg-secondary">User</span>
                    @endif
                </div>
            </div>

            <div class="d-grid gap-2 mb-4">
                <a href="{{ route('profile') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear"></i> Settings
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>

            <div id="stats-container">
                <!-- Stats cards will be loaded here -->
            </div>
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
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            {{ $slot ?? '' }}
            @yield('content')
                
            @endif
        </div>
    </div>

    @livewireScripts
    
    <!-- Notifications will be loaded via Vite in app.js -->
    
    <script>
        // Initialize sortable for stats cards
        document.addEventListener('DOMContentLoaded', function() {
            const statsContainer = document.getElementById('stats-container');
            if (statsContainer) {
                new Sortable(statsContainer, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        // Save new order to localStorage or send to server
                        const cardOrder = Array.from(statsContainer.children).map(card => card.dataset.cardId);
                        localStorage.setItem('stats-card-order', JSON.stringify(cardOrder));
                    }
                });
            }
        });
    </script>
</body>
</html>
