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

        /* Add this to your existing CSS in the <style> section */
        .room-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .room-item:hover {
            background-color: #f8f9fa !important;
        }

        .chat-room-header {
            background: #f8f9fa;
        }

        .chat-room-info {
            background: #f8f9fa;
        }

        .message-sender {
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: #495057;
        }

        .new-chat-modal {
            background: white;
            height: 100%;
        }

        .cursor-pointer {
            cursor: pointer;
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
                    <i class="bi bi-chat-dots"></i>
                    @if(Auth::user()->isSuperAdmin())
                    Admin Chat Center
                    @elseif(Auth::user()->hasRole('technician'))
                    Technician Support
                    @else
                    Customer Support
                    @endif
                    <span class="badge bg-success ms-auto" id="chat-status">Online</span>
                    <span class="badge bg-primary ms-1" id="unread-messages" style="display: none;">0</span>
                </div>
            
                <div class="chat-body" id="chat-body" style="display: none;">
                    <!-- Chat Room List -->
                    <div class="chat-room-list" id="chat-room-list">
                        <div class="chat-room-header d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h6 class="mb-0">
                                @if(Auth::user()->isSuperAdmin())
                                All Conversations
                                @elseif(Auth::user()->hasRole('technician'))
                                My Support Chats
                                @else
                                Support Tickets
                                @endif
                            </h6>
                            @if(!Auth::user()->isSuperAdmin())
                            <button class="btn btn-sm btn-primary" onclick="createNewChat()">
                                <i class="bi bi-plus"></i> New
                            </button>
                            @endif
                        </div>
                        <div class="room-list-container" id="room-list-container">
                            <div class="text-center p-3 text-muted">Loading chats...</div>
                        </div>
                    </div>
            
                    <!-- Active Chat Room -->
                    <div class="active-chat-room" id="active-chat-room" style="display: none;">
                        <div class="chat-room-info p-2 border-bottom bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-outline-secondary" onclick="showRoomList()">
                                    <i class="bi bi-arrow-left"></i>
                                </button>
                                <div class="flex-grow-1 ms-2">
                                    <div class="fw-bold" id="active-room-name">Chat Room</div>
                                    <div class="small text-muted" id="active-room-info">Loading...</div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="closeCurrentRoom()">
                                                <i class="bi bi-x-circle"></i> Close Chat
                                            </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
            
                        <div class="chat-messages" id="chat-messages">
                            <!-- Messages will be loaded here -->
                        </div>
            
                        <div class="chat-input-container">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="chat-input" placeholder="Type your message..."
                                    onkeypress="handleChatKeyPress(event)">
                                <button class="btn btn-primary" type="button" onclick="sendChatMessage()">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </div>
                    </div>
            
                    <!-- New Chat Modal Content -->
                    <div class="new-chat-modal" id="new-chat-modal" style="display: none;">
                        @if(!Auth::user()->hasRole('technician'))
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Start New Support Chat</h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="showRoomList()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-3">
                            <form id="new-chat-form">
                                <div class="mb-3">
                                    <label class="form-label">Ticket Number</label>
                                    <input type="text" class="form-control" id="ticket-id-input" 
                                    placeholder="Enter your ticket Number (e.g. SLX20250718-0001)"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Your Full Name</label>
                                    <input type="text" class="form-control" id="customer-name-input" placeholder="Enter your full name"
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Start Chat</button>
                            </form>
                        </div>
                        @else
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Connect with Admin</h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="showRoomList()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-3">
                            <button class="btn btn-primary w-100" onclick="createTechnicianRoom()">
                                Start Chat with Admin
                            </button>
                        </div>
                        @endif
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
    
    <!-- Enhanced Hierarchical Chat JavaScript -->
    <script>
        class HierarchicalChatManager {
            constructor() {
                this.currentRoom = null;
                this.rooms = [];
                this.userType = null;
                this.chatOpen = false;
                this.initialize();
            }

            async initialize() {
                await this.loadRooms();
                this.setupEventListeners();
                this.setupPusherChannels();
            }

            async loadRooms() {
                try {
                    const response = await fetch('/chat/rooms', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    
                    this.rooms = data.rooms || [];
                    this.userType = data.user_type;
                    this.renderRoomList();
                    this.updateUnreadBadge();
                } catch (error) {
                    console.error('Error loading rooms:', error);
                }
            }

            renderRoomList() {
                const container = document.getElementById('room-list-container');
                if (!container) return;

                if (this.rooms.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-3 text-muted">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                            No active chats
                        </div>
                    `;
                    return;
                }

                let html = '';
                this.rooms.forEach(room => {
                    const lastMessage = room.messages && room.messages.length > 0 ? room.messages[0] : null;
                    const unreadCount = this.getUnreadCount(room.id);
                    
                    html += `
                        <div class="room-item p-3 border-bottom cursor-pointer ${unreadCount > 0 ? 'bg-light' : ''}" 
                             onclick="openChatRoom(${room.id})">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${room.name}</div>
                                    <div class="small text-muted">
                                        ${this.getRoomTypeLabel(room)}
                                    </div>
                                    ${lastMessage ? `
                                        <div class="small mt-1">${lastMessage.message.substring(0, 50)}...</div>
                                    ` : ''}
                                </div>
                                <div class="text-end">
                                    ${unreadCount > 0 ? `<span class="badge bg-primary rounded-pill">${unreadCount}</span>` : ''}
                                    <div class="small text-muted">
                                        ${lastMessage ? this.formatTime(lastMessage.created_at) : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            }

            getRoomTypeLabel(room) {
                if (room.type === 'customer_support') {
                    if (room.ticket) {
                        return `Ticket #${room.ticket.id}`;
                    }
                    return 'Customer Support';
                } else if (room.type === 'technician_admin') {
                    if (room.technician) {
                        return `Technician: ${room.technician.name}`;
                    }
                    return 'Technician Chat';
                }
                return 'Chat';
            }

            async openChatRoom(roomId) {
                try {
                    const response = await fetch(`/chat/room/${roomId}/messages`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    
                    this.currentRoom = data.room;
                    this.renderChatRoom(data.messages);
                    this.showChatRoom();
                    this.setupRoomChannel(data.room.id);
                } catch (error) {
                    console.error('Error loading chat room:', error);
                }
            }

            renderChatRoom(messages) {
                const container = document.getElementById('chat-messages');
                const roomName = document.getElementById('active-room-name');
                const roomInfo = document.getElementById('active-room-info');
                
                if (roomName && this.currentRoom) {
                    roomName.textContent = this.currentRoom.name;
                }
                
                if (roomInfo && this.currentRoom) {
                    roomInfo.textContent = this.getRoomTypeLabel(this.currentRoom);
                }

                if (!container) return;

                container.innerHTML = '';
                messages.forEach(message => {
                    this.addMessageToUI(message);
                });
                
                this.scrollToBottom();
            }

            addMessageToUI(message) {
                const container = document.getElementById('chat-messages');
                if (!container) return;

                const messageDiv = document.createElement('div');
                const isOwnMessage = message.sender.id === window.Laravel.user.id;
                
                let messageClass = 'message ';
                if (isOwnMessage) {
                    messageClass += 'user-message';
                } else {
                    messageClass += 'support-message';
                }

                messageDiv.className = messageClass;
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${!isOwnMessage ? `<div class="message-sender">${message.sender.name} (${message.sender_type})</div>` : ''}
                        <div class="message-text">${message.message}</div>
                        <div class="message-time">${this.formatTime(message.created_at)}</div>
                    </div>
                `;
                
                container.appendChild(messageDiv);
                this.scrollToBottom();
            }

            async sendMessage() {
                const input = document.getElementById('chat-input');
                const message = input.value.trim();
                
                if (!message || !this.currentRoom) return;
                
                try {
                    const response = await fetch('/chat/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            room_id: this.currentRoom.id,
                            message: message
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        input.value = '';
                        // Message will be added via Pusher event
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                }
            }
           async createNewChat() {
            console.log('createNewChat called');
            
            const ticketIdInput = document.getElementById('ticket-id-input');
            const customerNameInput = document.getElementById('customer-name-input');
            
            console.log('Form elements:', {
            ticketIdInput: ticketIdInput,
            customerNameInput: customerNameInput
            });
            
            if (!ticketIdInput || !customerNameInput) {
            console.error('Form elements not found!');
            alert('Form elements not found. Please refresh the page.');
            return;
            }
            
            const ticketId = ticketIdInput.value?.trim();
            const customerName = customerNameInput.value?.trim();
            
            console.log('Form values:', { ticketId, customerName });
            
            if (!ticketId || !customerName) {
            alert('Please fill in both Ticket ID and Customer Name');
            return;
            }
            
            try {
            console.log('Sending request with data:', { ticketId, customerName });
            
            const response = await fetch('/chat/customer-support', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
            ticket_id: ticketId,
            customer_name: customerName
            })
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error response:', errorText);
                try {
                    const errorData = JSON.parse(errorText);
                    console.error('Parsed error:', errorData);
                    if (errorData.error) {
                        alert('Error: ' + errorData.error);
                    } else {
                        alert('Server error: ' + errorData.message || 'Unknown error');
                    }
                } catch (parseError) {
            console.error('Could not parse error response:', parseError);
            alert('Server error: ' + response.status + ' - Check console for details');
            }
            return;
            }
            
            const data = await response.json();
            console.log('Success response:', data);
            
            if (data.room) {
            // Clear form
            ticketIdInput.value = '';
            customerNameInput.value = '';
            
            await this.loadRooms();
            this.openChatRoom(data.room.id);
            } else if (data.error) {
            alert('Error: ' + data.error);
            }
            } catch (error) {
            console.error('Network error:', error);
            alert('Network error: ' + error.message);
            }
            }

            async createCustomerSupportChat(ticketId, customerName) {
                try {
                    const response = await fetch('/chat/customer-support', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ticket_id: ticketId,
                            customer_name: customerName
                        })
                    });

                    const data = await response.json();
                    if (data.room) {
                        await this.loadRooms();
                        this.openChatRoom(data.room.id);
                    }
                } catch (error) {
                    console.error('Error creating customer support chat:', error);
                }
            }

            async createTechnicianRoom() {
                console.log('createTechnicianRoom called');
                try {
                    console.log('Sending request to /chat/technician-room');
                    const response = await fetch('/chat/technician-room', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    console.log('Response status:', response.status);
                    const data = await response.json();
                    console.log('Response data:', data);
                    
                    if (data.room) {
                        console.log('Room created successfully:', data.room);
                        await this.loadRooms();
                        this.openChatRoom(data.room.id);
                    } else {
                        console.error('No room in response:', data);
                        alert('Error: No room created. Check console for details.');
                    }
                } catch (error) {
                    console.error('Error creating technician room:', error);
                    alert('Error creating technician room: ' + error.message);
                }
            }

            setupEventListeners() {
                const newChatForm = document.getElementById('new-chat-form');
                if (newChatForm) {
                    newChatForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        console.log('Form submitted');
                        this.createNewChat(); // Panggil method createNewChat yang sudah ada
                    });
            }
            }

            setupPusherChannels() {
                if (!window.Echo) return;

                // Listen for new messages in current room
                if (this.currentRoom) {
                    this.setupRoomChannel(this.currentRoom.id);
                }

                // Listen for global chat events
                const userId = window.Laravel.user.id;
                window.Echo.private(`user.${userId}`)
                    .listen('MessageSent', (e) => {
                        if (e.message.chat_room_id === this.currentRoom?.id) {
                            this.addMessageToUI(e);
                        }
                        this.loadRooms(); // Refresh room list
                    });
            }

            setupRoomChannel(roomId) {
                if (window.Echo) {
                    window.Echo.private(`chat-room.${roomId}`)
                        .listen('MessageSent', (e) => {
                            this.addMessageToUI(e);
                        });
                }
            }

            showRoomList() {
                document.getElementById('chat-room-list').style.display = 'block';
                document.getElementById('active-chat-room').style.display = 'none';
                document.getElementById('new-chat-modal').style.display = 'none';
                this.currentRoom = null;
            }

            showChatRoom() {
                document.getElementById('chat-room-list').style.display = 'none';
                document.getElementById('active-chat-room').style.display = 'block';
                document.getElementById('new-chat-modal').style.display = 'none';
                setTimeout(() => {
                    document.getElementById('chat-input')?.focus();
                }, 100);
            }

            showNewChatModal() {
                document.getElementById('chat-room-list').style.display = 'none';
                document.getElementById('active-chat-room').style.display = 'none';
                document.getElementById('new-chat-modal').style.display = 'block';
            }

            getUnreadCount(roomId) {
                return 0; // Placeholder
            }

            updateUnreadBadge() {
                const totalUnread = this.rooms.reduce((total, room) => {
                    return total + this.getUnreadCount(room.id);
                }, 0);

                const badge = document.getElementById('unread-messages');
                if (totalUnread > 0) {
                    badge.textContent = totalUnread;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }

            formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }

            scrollToBottom() {
                const container = document.getElementById('chat-messages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        }

        // Global chat manager instance
        let hierarchicalChatManager;

        // Global functions for onclick events
        function toggleChat() {
            const chatBody = document.getElementById('chat-body');
            const isOpen = chatBody.style.display !== 'none';
            
            if (isOpen) {
                chatBody.style.display = 'none';
            } else {
                chatBody.style.display = 'flex';
                if (!hierarchicalChatManager) {
                    hierarchicalChatManager = new HierarchicalChatManager();
                } else {
                    hierarchicalChatManager.showRoomList();
                }
            }
        }

        function openChatRoom(roomId) {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.openChatRoom(roomId);
            }
        }

        function createNewChat() {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.showNewChatModal();
            }
        }

        function createTechnicianRoom() {
            console.log('Global createTechnicianRoom called');
            console.log('hierarchicalChatManager:', hierarchicalChatManager);
            if (hierarchicalChatManager) {
                hierarchicalChatManager.createTechnicianRoom();
            } else {
                console.error('hierarchicalChatManager is not initialized');
                alert('Chat manager not initialized. Please refresh the page.');
            }
        }

        function showRoomList() {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.showRoomList();
            }
        }

        function sendChatMessage() {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.sendMessage();
            }
        }

        function handleChatKeyPress(event) {
            if (event.key === 'Enter') {
                sendChatMessage();
            }
        }

        function closeCurrentRoom() {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.showRoomList();
            }
        }

        // Debug function
        function debugChat() {
            console.log('=== CHAT DEBUG INFO ===');
            console.log('hierarchicalChatManager:', hierarchicalChatManager);
            console.log('window.Laravel:', window.Laravel);
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
            console.log('Current URL:', window.location.href);
            
            // Test basic fetch
            fetch('/chat/rooms', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Test fetch status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Test fetch data:', data);
            })
            .catch(error => {
                console.error('Test fetch error:', error);
            });
        }

        // Keep existing chat functions for backward compatibility
        function sendMessage() {
            sendChatMessage();
        }
        
        function addMessage(text, type) {
            if (hierarchicalChatManager && hierarchicalChatManager.currentRoom) {
                const message = {
                    message: text,
                    sender: window.Laravel.user,
                    sender_type: type,
                    created_at: new Date().toISOString()
                };
                hierarchicalChatManager.addMessageToUI(message);
            }
        }
        
        function scrollToBottom() {
            if (hierarchicalChatManager) {
                hierarchicalChatManager.scrollToBottom();
            }
        }
        
        // Initialize chat status
        document.addEventListener('DOMContentLoaded', function() {
            const statusBadge = document.getElementById('chat-status');
            if (statusBadge) {
                const isOnline = true;
                
                if (isOnline) {
                    statusBadge.textContent = 'Online';
                    statusBadge.className = 'badge bg-success ms-auto';
                } else {
                    statusBadge.textContent = 'Offline';
                    statusBadge.className = 'badge bg-secondary ms-auto';
                }
            }
        });

       
        </script>

    <!-- Keep existing notification scripts -->
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
        
        console.log('✅ Laravel user set:', window.Laravel.user);
    </script>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof NotificationManager !== 'undefined') {
                window.notificationManager = new NotificationManager();
                console.log('✅ NotificationManager initialized and available globally');
                
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

        window.showNotificationToast = function(title, message, type = 'info') {
            if (window.notificationManager) {
                window.notificationManager.showToast(title, message, type);
            } else {
                console.warn('NotificationManager not available');
            }
        };

        window.testNotification = function() {
            if (window.notificationManager) {
                window.notificationManager.showToast('Test Notification', 'This is a test notification from the system.', 'success');
                
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
