import axios from 'axios';

class NotificationManager {
    constructor() {
        console.log('🔔 NotificationManager initialized');
        this.notifications = [];
        this.isConnected = false;
        this.init();
    }

    init() {
        this.loadNotifications();
        this.setupEcho();
        this.setupUI();
        console.log('🔔 NotificationManager setup complete');
    }

    async setupEcho() {
        try {
            // Import Echo dan Pusher secara dynamic
            const { default: Echo } = await import('laravel-echo');
            const { default: Pusher } = await import('pusher-js');

            window.Pusher = Pusher;

            // Setup Echo dengan konfigurasi yang tepat
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY || '93a2ffb34b52d8bd9fb5',
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1',
                forceTLS: true,
                encrypted: true,
                authorizer: (channel, options) => {
                    return {
                        authorize: (socketId, callback) => {
                            axios.post('/broadcasting/auth', {
                                socket_id: socketId,
                                channel_name: channel.name
                            }, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => {
                                callback(false, response.data);
                            })
                            .catch(error => {
                                console.error('Broadcasting auth error:', error);
                                callback(true, error);
                            });
                        }
                    };
                },
            });

            // Setup connection listeners
            this.setupConnectionListeners();
            
            // Setup broadcast listeners
            this.setupBroadcastListeners();
            
            console.log('✅ Echo setup complete');
        } catch (error) {
            console.error('❌ Failed to setup Echo:', error);
            this.isConnected = false;
        }
    }

    setupConnectionListeners() {
        if (window.Echo && window.Echo.connector) {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                this.isConnected = true;
                console.log('✅ NotificationManager: Pusher connected');
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                this.isConnected = false;
                console.log('❌ NotificationManager: Pusher disconnected');
            });

            window.Echo.connector.pusher.connection.bind('error', (error) => {
                console.error('❌ NotificationManager: Pusher error:', error);
                this.isConnected = false;
            });
        }
    }

    setupBroadcastListeners() {
        if (!window.Echo) return;

        try {
            // Listen untuk ticket created events
            window.Echo.channel('tickets')
                .listen('.ticket.created', (e) => {
                    console.log('🎯 Received ticket.created event:', e);
                    this.handleTicketCreated(e);
                });

            // Listen untuk private user notifications
            if (window.Laravel && window.Laravel.user) {
                window.Echo.private(`App.Models.User.${window.Laravel.user.id}`)
                    .notification((notification) => {
                        console.log('🎯 Received private notification:', notification);
                        this.handlePrivateNotification(notification);
                    });
            }
                
            console.log('✅ Broadcasting listeners setup complete');
        } catch (error) {
            console.error('❌ Failed to setup broadcast listeners:', error);
        }
    }

    handleTicketCreated(eventData) {
        console.log('🎫 Handling ticket created event:', eventData);
        
        // Create notification object
        const notification = {
            id: `event_${Date.now()}`,
            type: 'App\\Events\\TicketCreated',
            data: {
                ticket_id: eventData.ticket.id,
                ticket_number: eventData.ticket.ticket_number,
                title: 'New Ticket Created',
                message: eventData.message,
                type: eventData.type,
                ticket: eventData.ticket
            },
            created_at: new Date().toISOString(),
            read_at: null
        };

        this.addNotification(notification);
    }

    handlePrivateNotification(notification) {
        console.log('🔔 Handling private notification:', notification);
        
        // Format notification sesuai dengan struktur database
        const formattedNotification = {
            id: notification.id || `private_${Date.now()}`,
            type: notification.type,
            data: notification,
            created_at: new Date().toISOString(),
            read_at: null
        };

        this.addNotification(formattedNotification);
    }

    addNotification(notification) {
        console.log('➕ Adding notification:', notification);
        
        // Tambahkan ke array notifications
        this.notifications.unshift(notification);
        
        // Sort ulang berdasarkan created_at
        this.notifications.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
        
        // Update UI
        this.updateNotificationUI();
        this.updateNotificationCount(this.notifications.filter(n => !n.read_at).length);
        
        // Show toast notification
        this.showToast(notification);
    }

    async loadNotifications() {
        try {
            const response = await fetch('/notifications');
            const data = await response.json();
            
            this.notifications = (data.notifications || []).sort((a, b) => {
                return new Date(b.created_at) - new Date(a.created_at);
            });
            
            this.updateNotificationUI();
            this.updateNotificationCount(data.unread_count || 0);
            
            console.log('📱 Loaded notifications:', this.notifications.length);
        } catch (error) {
            console.error('❌ Error loading notifications:', error);
            this.showErrorInDropdown();
        }
    }

    updateNotificationUI() {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (!notificationDropdown) return;

        // Show connection status
        const connectionStatus = this.isConnected ? 
            '<small class="text-success">🟢 Live</small>' : 
            '<small class="text-warning">🟡 Offline</small>';

        if (this.notifications.length === 0) {
            notificationDropdown.innerHTML = `
                <li>
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications ${connectionStatus}</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">Mark All Read</button>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><span class="dropdown-item-text text-muted">No notifications</span></li>
            `;
            return;
        }

        let html = `
            <li>
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span>Notifications ${connectionStatus}</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">Mark All Read</button>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
        `;

        this.notifications.slice(0, 10).forEach(notification => {
            const data = notification.data || notification;
            const isUnread = !notification.read_at;
            
            html += `
                <li>
                    <a class="dropdown-item ${isUnread ? 'fw-bold bg-light' : ''}" href="#" onclick="markAsRead('${notification.id}')">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-ticket-perforated me-2 mt-1 text-primary"></i>
                            <div class="flex-grow-1">
                                <div class="small">${data.title || 'New Notification'}</div>
                                <div class="text-muted small">${data.message || 'You have a new notification'}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    ${this.formatTimeAgo(notification.created_at)}
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

        notificationDropdown.innerHTML = html;
    }

    updateNotificationCount(count) {
        const unreadBadge = document.getElementById('unreadBadge');
        if (unreadBadge) {
            if (count > 0) {
                unreadBadge.textContent = count;
                unreadBadge.style.display = 'inline-block';
            } else {
                unreadBadge.style.display = 'none';
            }
        }
    }

    showToast(notification) {
        const data = notification.data || notification;
        const title = data.title || 'New Notification';
        const message = data.message || 'You have a new notification';
        
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'toast show align-items-center text-white bg-primary border-0';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-bell-fill me-2"></i>
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    showErrorInDropdown() {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.innerHTML = `
                <li>
                    <div class="dropdown-header">
                        <span>Notifications</span>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><span class="dropdown-item-text text-danger">Error loading notifications</span></li>
            `;
        }
    }

    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }

    setupUI() {
        const notificationBell = document.getElementById('notificationBell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                this.loadNotifications();
            });
        }
    }
}

// Global functions
window.markAsRead = async function(notificationId) {
    if (notificationId.startsWith('event_') || notificationId.startsWith('private_')) {
        window.notificationManager.loadNotifications();
        return;
    }
    
    try {
        const response = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (response.ok) {
            window.notificationManager.loadNotifications();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

window.markAllAsRead = async function() {
    try {
        const response = await fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (response.ok) {
            window.notificationManager.loadNotifications();
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM loaded, initializing NotificationManager...');
    window.notificationManager = new NotificationManager();
});

export default NotificationManager;