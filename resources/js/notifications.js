import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Laravel Echo is already initialized in bootstrap.js
// We'll use the existing window.Echo instance

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.init();
    }

    init() {
        this.setupEventListeners();
        // Add delay before first load to prevent immediate redirect issues
        setTimeout(() => {
            this.loadUnreadNotifications();
        }, 2000);
        this.setupRealtimeListeners();
    }

    setupEventListeners() {
        // Mark notification as read when clicked
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item[data-notification-id]')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                this.markAsRead(notificationId);
            }
        });

        // Mark all as read button
        const markAllReadBtn = document.getElementById('markAllRead');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Notification dropdown toggle
        const notificationBell = document.getElementById('notificationBell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                this.loadNotifications();
            });
        }
    }

    setupRealtimeListeners() {
        // Add connection status logging
        if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
            const pusher = window.Echo.connector.pusher;
            
            pusher.connection.bind('connected', () => {
                console.log('✅ Pusher connected successfully');
            });
            
            pusher.connection.bind('disconnected', () => {
                console.log('❌ Pusher disconnected');
            });
            
            pusher.connection.bind('error', (error) => {
                console.error('❌ Pusher connection error:', error);
            });
            
            pusher.connection.bind('state', (state) => {
                console.log('📡 Pusher connection state:', state);
            });
        }

        // Listen for new ticket created - using public channel
        const ticketsChannel = window.Echo.channel('tickets');
        
        ticketsChannel.subscribed(() => {
            console.log('✅ Successfully subscribed to tickets channel');
        });

        ticketsChannel.error((error) => {
            console.error('❌ Error subscribing to tickets channel:', error);
        });

        ticketsChannel.listen('.ticket.created', (e) => {
                console.log('Received ticket.created event:', e);
                this.showToast('New Ticket Created', e.message, 'info');
                this.addNotification({
                    type: 'ticket_created',
                    message: e.message,
                    data: e.ticket,
                    read_at: null,
                    created_at: new Date().toISOString()
                });
                this.playNotificationSound();
                // Reload the unread count
                this.loadUnreadNotifications();
            });

        // Listen for ticket assignments
        ticketsChannel.listen('.ticket.assigned', (e) => {
                console.log('Received ticket.assigned event:', e);
                this.showToast('Ticket Assigned', e.message, 'warning');
                this.addNotification({
                    type: 'ticket_assigned',
                    message: e.message,
                    data: e.ticket,
                    read_at: null,
                    created_at: new Date().toISOString()
                });
                this.playNotificationSound();
                // Reload the unread count
                this.loadUnreadNotifications();
            });

        // Listen for status changes
        ticketsChannel.listen('.ticket.status.changed', (e) => {
                console.log('Received ticket.status.changed event:', e);
                this.showToast('Ticket Status Changed', e.message, 'info');
                this.addNotification({
                    type: 'ticket_status_changed',
                    message: e.message,
                    data: e.ticket,
                    read_at: null,
                    created_at: new Date().toISOString()
                });
                // Reload the unread count
                this.loadUnreadNotifications();
            });

        // Listen for private notifications (user-specific)
        if (window.userId) {
            const privateChannel = window.Echo.private(`user.${window.userId}`);
            
            privateChannel.subscribed(() => {
                console.log(`✅ Successfully subscribed to private user.${window.userId} channel`);
            });

            privateChannel.error((error) => {
                console.error(`❌ Error subscribing to private user.${window.userId} channel:`, error);
            });

            privateChannel.notification((notification) => {
                    console.log('Received private notification:', notification);
                    this.handlePrivateNotification(notification);
                });
        } else {
            console.warn('⚠️ No userId found, private notifications will not work');
        }
    }

    handlePrivateNotification(notification) {
        const typeMap = {
            'App\\Notifications\\TicketCreated': 'success',
            'App\\Notifications\\TicketAssigned': 'warning',
            'App\\Notifications\\TicketStatusChanged': 'info'
        };

        const type = typeMap[notification.type] || 'info';
        this.showToast('Notification', notification.message || 'You have a new notification', type);
        this.addNotification(notification);
        this.playNotificationSound();
    }

    addNotification(notification) {
        this.notifications.unshift(notification);
        if (!notification.read_at) {
            this.unreadCount++;
            this.updateUnreadBadge();
        }
        this.updateNotificationDropdown();
    }

    showToast(title, message, type = 'info') {
        // Check if user has enabled browser notifications
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: 'ticket-notification'
            });
        }

        // Show in-app toast
        this.createToast(title, message, type);
    }

    createToast(title, message, type) {
        const toastContainer = this.getOrCreateToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        
        // Show toast with simple fade effect
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            toast.style.opacity = '1';
        }, 100);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }, 5000);

        // Add close button functionality
        const closeBtn = toast.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            });
        }
    }

    getOrCreateToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }

    playNotificationSound() {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore autoplay errors
        });
    }

    loadUnreadNotifications() {
        fetch('/notifications/unread-count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.unreadCount = data.count;
                this.updateUnreadBadge();
            })
            .catch(error => console.error('Error loading unread count:', error));
    }

    loadNotifications() {
        fetch('/notifications', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.notifications = data.notifications;
                this.updateNotificationDropdown();
            })
            .catch(error => console.error('Error loading notifications:', error));
    }

    markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(() => {
            // Update local state
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString();
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateUnreadBadge();
                this.updateNotificationDropdown();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    markAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(() => {
            this.notifications.forEach(notification => {
                notification.read_at = new Date().toISOString();
            });
            this.unreadCount = 0;
            this.updateUnreadBadge();
            this.updateNotificationDropdown();
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }

    updateUnreadBadge() {
        const badge = document.getElementById('unreadBadge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    updateNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) return;

        if (this.notifications.length === 0) {
            dropdown.innerHTML = '<li><span class="dropdown-item-text text-muted">No notifications</span></li>';
            return;
        }

        const items = this.notifications.slice(0, 10).map(notification => {
            const isRead = notification.read_at !== null;
            const timeAgo = this.formatTimeAgo(notification.created_at);
            
            return `
                <li>
                    <a class="dropdown-item notification-item ${isRead ? '' : 'bg-light'}" 
                       href="#" 
                       data-notification-id="${notification.id}">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-bold">${this.getNotificationTitle(notification.type)}</div>
                                <div class="small text-muted">${notification.message || notification.data?.message || ''}</div>
                                <div class="small text-muted">${timeAgo}</div>
                            </div>
                            ${!isRead ? '<div class="badge bg-primary rounded-pill">New</div>' : ''}
                        </div>
                    </a>
                </li>
            `;
        }).join('');

        const markAllButton = this.unreadCount > 0 ? `
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item text-center" id="markAllRead">Mark all as read</button></li>
        ` : '';

        dropdown.innerHTML = items + markAllButton;
    }

    getNotificationTitle(type) {
        const titles = {
            'ticket_created': 'New Ticket',
            'ticket_assigned': 'Ticket Assigned',
            'ticket_status_changed': 'Status Changed'
        };
        return titles[type] || 'Notification';
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

    // Request notification permission
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
    
    // Request notification permission
    if ('Notification' in window) {
        window.notificationManager.requestNotificationPermission();
    }
});