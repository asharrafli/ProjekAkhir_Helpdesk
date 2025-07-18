<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
     <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">
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

    <!-- Custom styles stack -->
    @stack('styles')

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
         /* Chat Widget Styles */
        .chat-widget {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .chat-header {
            background: #f8f9fa;
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .chat-header:hover {
            background: #e9ecef;
        }
        
        .chat-header i {
            color: #28a745;
            margin-right: 8px;
        }
        
        .chat-body {
            height: 300px;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            max-height: 240px;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 12px;
            padding: 8px 12px;
            border-radius: 12px;
            max-width: 80%;
            word-wrap: break-word;
        }
        
        .message.user-message {
            background: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        
        .message.support-message {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
        }
        
        .message.system-message {
            background: #e3f2fd;
            color: #1976d2;
            text-align: center;
            font-style: italic;
            margin: 0 auto;
        }
        
        .chat-input-container {
            padding: 12px 16px;
            border-top: 1px solid #e9ecef;
            background: white;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 4px;
        }
        
        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
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
                        @can('view-assigned-tickets')
                        <li><a class="dropdown-item" href="{{ route('tickets.assigned') }}">My Assigned Ticket</a></li>
                        @endcan
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
                        <li>
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <button class="btn btn-sm btn-outline-primary" data-action="mark-all-read">Mark All Read</button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
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
            {{-- chat --}}
            <div class="chat-widget">
                <div class="chat-header" onclick="toggleChat()">
                    <i class="bi bi-chat-dots"></i> Support Chat
                    <span class="badge bg-success ms-auto" id="chat-status">Online</span>
                </div>
                <div class="chat-body" id="chat-body" style="display: none;">
                    <div class="chat-messages" id="chat-messages">
                        <div class="message system-message">
                            <small class="text-muted">Welcome! How can we help you today?</small>
                        </div>
                    </div>
                    <div class="chat-input-container">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="chat-input" placeholder="Type your message..." onkeypress="handleChatKeyPress(event)">
                            <button class="btn btn-primary" type="button" onclick="sendMessage()">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
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
    
    <!-- Custom scripts stack -->
    @stack('scripts')
    <script>
        // Chat functionality
        let chatOpen = false;
        
        function toggleChat() {
            const chatBody = document.getElementById('chat-body');
            chatOpen = !chatOpen;
            
            if (chatOpen) {
                chatBody.style.display = 'flex';
                scrollToBottom();
                document.getElementById('chat-input').focus();
            } else {
                chatBody.style.display = 'none';
            }
        }
        
        function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Add user message
            addMessage(message, 'user');
            input.value = '';
            
            // Simulate support response (replace with actual chat API)
            setTimeout(() => {
                const responses = [
                    "Thanks for reaching out! How can I assist you?",
                    "I understand your concern. Let me help you with that.",
                    "Could you provide more details about your issue?",
                    "I'll escalate this to our technical team.",
                    "Is there anything else I can help you with?"
                ];
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                addMessage(randomResponse, 'support');
            }, 1000);
        }
        
        function addMessage(text, type) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            const currentTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageDiv.className = `message ${type}-message`;
            messageDiv.innerHTML = `
                <div>${text}</div>
                <div class="message-time">${currentTime}</div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            scrollToBottom();
        }
        
        function scrollToBottom() {
            const messagesContainer = document.getElementById('chat-messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function handleChatKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
        
        // Initialize chat status
        document.addEventListener('DOMContentLoaded', function() {
            // Set online status
            const statusBadge = document.getElementById('chat-status');
            const isOnline = true; // You can determine this based on your logic
            
            if (isOnline) {
                statusBadge.textContent = 'Online';
                statusBadge.className = 'badge bg-success ms-auto';
            } else {
                statusBadge.textContent = 'Offline';
                statusBadge.className = 'badge bg-secondary ms-auto';
            }
        });
        
        // ...existing code...
    </script>
    <!-- Notifications will be loaded via Vite in app.js -->
    
    {{-- <script>
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

            // Initialize notification system
            initializeNotifications();
        });

        function initializeNotifications() {
            // Debug Pusher connection
            console.log('Initializing notifications...');
            console.log('Echo available:', !!window.Echo);
            
            if (window.Echo) {
                console.log('Pusher state:', window.Echo.connector.pusher.connection.state);
                
                // Listen for connection events
                window.Echo.connector.pusher.connection.bind('connected', function() {
                    console.log('Pusher connected successfully!');
                });
                
                window.Echo.connector.pusher.connection.bind('disconnected', function() {
                    console.log('Pusher disconnected');
                });
                
                window.Echo.connector.pusher.connection.bind('error', function(err) {
                    console.error('Pusher connection error:', err);
                });
            }
            
            // Load unread notifications count
            loadUnreadCount();
            
            // Load notifications when dropdown is opened
            const notificationBell = document.getElementById('notificationBell');
            if (notificationBell) {
                notificationBell.addEventListener('click', function() {
                    loadNotifications();
                });
            }
            
            // Listen for new notifications via Pusher
            @auth
            if (window.Echo) {
                console.log('Setting up private channel for user {{ Auth::id() }}');
                
                // Test both channel names
                window.Echo.private(`App.Models.User.{{ Auth::id() }}`)
                    .notification((notification) => {
                        console.log('New notification received (App.Models.User):', notification);
                        showToast(notification.message || 'You have a new notification!');
                        loadUnreadCount();
                        updateNotificationDropdown(notification);
                    });
                
                // Also try listening to public tickets channel
                window.Echo.channel('tickets')
                    .listen('.ticket.created', (e) => {
                        console.log('Ticket created event received:', e);
                        showToast('New ticket created: ' + e.message);
                        loadUnreadCount();
                    });
            } else {
                console.error('Echo is not available. Check if Pusher is loaded correctly.');
            }
            @endauth
            
            // Refresh notifications every 30 seconds
            setInterval(loadUnreadCount, 30000);
        }

        function loadUnreadCount() {
            fetch('{{ route("notifications.unread-count") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('unreadBadge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading unread count:', error);
            });
        }

        function loadNotifications() {
            fetch('{{ route("notifications.index") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                updateNotificationList(data.notifications || []);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                document.getElementById('notificationDropdown').innerHTML = 
                    '<li><span class="dropdown-item-text text-danger">Error loading notifications</span></li>';
            });
        }

        function updateNotificationList(notifications) {
            const dropdown = document.getElementById('notificationDropdown');
            if (!dropdown) return;

            if (notifications.length === 0) {
                dropdown.innerHTML = '<li><span class="dropdown-item-text text-muted">No notifications</span></li>';
                return;
            }

            let html = '';
            notifications.forEach(notification => {
                const isUnread = !notification.read_at;
                const data = notification.data || {};
                
                html += `
                    <li>
                        <a class="dropdown-item ${isUnread ? 'fw-bold bg-light' : ''}" href="#" onclick="markAsRead('${notification.id}')">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-bell me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="small">${data.message || 'New notification'}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        ${formatTimeAgo(notification.created_at)}
                                    </div>
                                </div>
                                ${isUnread ? '<span class="badge bg-primary rounded-pill">New</span>' : ''}
                            </div>
                        </a>
                    </li>
                `;
            });

            html += `
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="#" onclick="markAllAsRead()">Mark all as read</a></li>
            `;

            dropdown.innerHTML = html;
        }

        function updateNotificationDropdown(newNotification) {
            // Add new notification to the top of dropdown if it's open
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                loadNotifications(); // Reload all notifications
            }
        }

        function markAsRead(notificationId) {
            fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUnreadCount();
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        function markAllAsRead() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUnreadCount();
                    loadNotifications();
                    showToast('All notifications marked as read');
                }
            })
            .catch(error => console.error('Error marking all as read:', error));
        }

        function showToast(message) {
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            // Create toast
            const toast = document.createElement('div');
            toast.className = 'toast show align-items-center text-white bg-primary border-0';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-bell-fill me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Remove toast after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 5000);
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            return `${Math.floor(diffInSeconds / 86400)}d ago`;
        }
    </script> --}}
    @auth
    <script>
        window.Laravel = {
            user: {
                id: {{ auth()->id() }},
                name: "{{ auth()->user()->name }}",
                email: "{{ auth()->user()->email }}",
                roles: @json(auth()->user()->getRoleNames()),
            }
        };
        
        // Debug log
        console.log('✅ Laravel user set:', window.Laravel.user);
    </script>
    @endauth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notification manager
            if (typeof NotificationManager !== 'undefined') {
                window.notificationManager = new NotificationManager();
                console.log('✅ NotificationManager initialized and available globally');
                
                // Debug Pusher connection
                if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                    console.log('Pusher connection state:', window.Echo.connector.pusher.connection.state);
                    
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        console.log('✅ Pusher connected successfully');
                    });
                    
                    window.Echo.connector.pusher.connection.bind('error', (err) => {
                        console.error('❌ Pusher connection error:', err);
                    });
                }
            } else {
                console.warn('⚠️ NotificationManager not found - check if notifications.js is loaded');
            }
        });

        // Make notification functions available globally
        window.showNotificationToast = function(title, message, type = 'info') {
            if (window.notificationManager) {
                window.notificationManager.showToast(title, message, type);
            } else {
                console.warn('NotificationManager not available');
            }
        };

        // Test notification function
        window.testNotification = function() {
            if (window.notificationManager) {
                // Show local toast first
                window.notificationManager.showToast('Test Notification', 'This is a test notification from the system.', 'success');
                
                // Also trigger backend notification
                fetch('/admin/test/trigger-notification', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Backend notification triggered:', data.message);
                    } else {
                        console.error('❌ Backend notification failed:', data.error);
                    }
                })
                .catch(error => {
                    console.error('❌ Error triggering backend notification:', error);
                });
            } else {
                alert('NotificationManager not available');
            }
        };
    </script>


</body>
</html>
