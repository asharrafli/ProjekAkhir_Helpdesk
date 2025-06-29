// Simple notification system without real-time broadcasting
class SimpleNotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.pollingInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadNotifications();
        this.startPolling();
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
        document.addEventListener('click', (e) => {
            if (e.target.id === 'markAllRead') {
                this.markAllAsRead();
            }
        });

        // Notification dropdown toggle
        const notificationBell = document.getElementById('notificationBell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                this.loadNotifications();
            });
        }
    }

    startPolling() {
        // Add delay before first load to prevent immediate redirect issues
        setTimeout(() => {
            this.loadUnreadCount();
        }, 2000);
        
        // Then poll every 30 seconds
        setInterval(() => {
            this.loadUnreadCount();
        }, this.pollingInterval);
        
        // Poll notifications when dropdown is opened
        const notificationBell = document.getElementById('notificationBell');
        if (notificationBell) {
            notificationBell.addEventListener('show.bs.dropdown', () => {
                this.loadNotifications();
            });
        }
    }

    showToast(title, message, type = 'info') {
        this.createToast(title, message, type);
    }

    createToast(title, message, type) {
        const toastContainer = this.getOrCreateToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 show`;
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
        
        // Show toast with fade effect
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

    loadUnreadCount() {
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
                const oldCount = this.unreadCount;
                this.unreadCount = data.count;
                this.updateUnreadBadge();
                
                // Show toast if new notifications arrived
                if (data.count > oldCount && oldCount > 0) {
                    this.showToast('New Notification', 'You have new notifications', 'info');
                }
            })
            .catch(error => {
                console.error('Error loading unread count:', error);
            });
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
            .catch(error => {
                console.error('Error loading notifications:', error);
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown) {
                    dropdown.innerHTML = '<li><span class="dropdown-item-text text-danger">Error loading notifications</span></li>';
                }
            });
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
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.simpleNotificationManager = new SimpleNotificationManager();
    console.log('Simple notification manager initialized');
});