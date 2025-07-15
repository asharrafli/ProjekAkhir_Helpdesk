@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>🔔 Notification Test Dashboard</h4>
                </div>
                
                <div class="card-body">
                    <!-- Test Buttons -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Test Notifications:</h5>
                            <button class="btn btn-primary me-2" onclick="testDatabaseNotifications()">
                                📧 Test Database Notifications
                            </button>
                            <button class="btn btn-success me-2" onclick="createTestTicket()">
                                🎫 Create Test Ticket
                            </button>
                            <button class="btn btn-info me-2" onclick="loadNotifications()">
                                🔄 Refresh Notifications
                            </button>
                            <button class="btn btn-warning me-2" onclick="markAllAsRead()">
                                ✅ Mark All Read
                            </button>
                        </div>
                    </div>

                    <!-- Notifications Display -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>📨 Database Notifications</h5>
                            <div id="database-notifications" class="border p-3" style="height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>📡 Broadcast Notifications</h5>
                            <div id="broadcast-notifications" class="border p-3" style="height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Waiting for broadcast...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Connection Status -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6>🔌 Connection Status:</h6>
                                <div id="connection-status">
                                    <span class="text-warning">⚠️ Checking connection...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let broadcastNotifications = [];
let notificationCount = 0;

// DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Notification test page loaded');
    
    // Load initial notifications
    loadNotifications();
    
    // Check connection status
    checkConnectionStatus();
    
    // Setup broadcast listeners if available
    if (window.Echo) {
        setupBroadcastListeners();
    }
});

function loadNotifications() {
    console.log('📡 Loading database notifications...');
    
    fetch('/api/notifications', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Notifications loaded:', data);
        displayDatabaseNotifications(data.notifications || []);
    })
    .catch(error => {
        console.error('❌ Error loading notifications:', error);
        document.getElementById('database-notifications').innerHTML = 
            '<div class="alert alert-danger">Failed to load notifications</div>';
    });
}

function displayDatabaseNotifications(notifications) {
    const container = document.getElementById('database-notifications');
    
    if (notifications.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">No notifications found</div>';
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const data = notification.data;
        const isRead = notification.read_at !== null;
        
        html += `
            <div class="notification-item mb-2 p-2 border rounded ${isRead ? 'bg-light' : 'bg-warning-light'}">
                <div class="d-flex justify-content-between">
                    <strong>${data.title || 'Notification'}</strong>
                    <small class="text-muted">${formatDate(notification.created_at)}</small>
                </div>
                <div class="mt-1">${data.message || 'No message'}</div>
                ${!isRead ? '<span class="badge bg-primary">New</span>' : ''}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function testDatabaseNotifications() {
    console.log('🧪 Testing database notifications...');
    
    fetch('/api/test-notification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Test notification response:', data);
        if (data.success) {
            alert('✅ Test notification sent successfully!');
            setTimeout(loadNotifications, 1000);
        } else {
            alert('❌ Failed to send test notification: ' + data.message);
        }
    })
    .catch(error => {
        console.error('❌ Error sending test notification:', error);
        alert('❌ Error sending test notification');
    });
}

function createTestTicket() {
    console.log('🎫 Creating test ticket...');
    
    const testData = {
        category_id: 1,
        title: 'Test Ticket - ' + new Date().toLocaleString(),
        description_ticket: 'This is a test ticket created for notification testing purposes.',
        priority: 'medium'
    };
    
    fetch('/tickets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(testData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Test ticket response:', data);
        if (data.success || response.ok) {
            alert('✅ Test ticket created successfully!');
            setTimeout(loadNotifications, 2000);
        } else {
            alert('❌ Failed to create test ticket: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Error creating test ticket:', error);
        alert('❌ Error creating test ticket');
    });
}

function markAllAsRead() {
    console.log('✅ Marking all notifications as read...');
    
    fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Mark all read response:', data);
        if (data.success) {
            alert('✅ All notifications marked as read!');
            loadNotifications();
        } else {
            alert('❌ Failed to mark notifications as read');
        }
    })
    .catch(error => {
        console.error('❌ Error marking notifications as read:', error);
        alert('❌ Error marking notifications as read');
    });
}

function setupBroadcastListeners() {
    console.log('📡 Setting up broadcast listeners...');
    
    // Listen to tickets channel
    window.Echo.channel('tickets')
        .listen('.ticket.created', (data) => {
            console.log('🎫 Ticket created broadcast received:', data);
            addBroadcastNotification('Ticket Created', data.message || 'New ticket created');
        });
    
    // Listen to global notifications
    window.Echo.channel('notifications.global')
        .listen('.ticket.created', (data) => {
            console.log('🌍 Global notification received:', data);
            addBroadcastNotification('Global Notification', data.message || 'Global notification received');
        });
    
    // Listen to user-specific notifications
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    if (userId) {
        window.Echo.private(`notifications.${userId}`)
            .listen('.ticket.created', (data) => {
                console.log('👤 Personal notification received:', data);
                addBroadcastNotification('Personal Notification', data.message || 'Personal notification received');
            });
    }
    
    console.log('✅ Broadcast listeners setup complete');
}

function addBroadcastNotification(title, message) {
    const notification = {
        title: title,
        message: message,
        timestamp: new Date(),
        id: ++notificationCount
    };
    
    broadcastNotifications.unshift(notification);
    displayBroadcastNotifications();
}

function displayBroadcastNotifications() {
    const container = document.getElementById('broadcast-notifications');
    
    if (broadcastNotifications.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">No broadcast notifications received</div>';
        return;
    }
    
    let html = '';
    broadcastNotifications.forEach(notification => {
        html += `
            <div class="notification-item mb-2 p-2 border rounded bg-info-light">
                <div class="d-flex justify-content-between">
                    <strong>${notification.title}</strong>
                    <small class="text-muted">${formatDate(notification.timestamp)}</small>
                </div>
                <div class="mt-1">${notification.message}</div>
                <span class="badge bg-info">Live</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function checkConnectionStatus() {
    const statusDiv = document.getElementById('connection-status');
    
    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        const state = window.Echo.connector.pusher.connection.state;
        statusDiv.innerHTML = `
            <span class="text-success">✅ Echo connected (${state})</span><br>
            <span class="text-info">📡 Pusher app key: ${window.Echo.connector.pusher.key}</span>
        `;
    } else {
        statusDiv.innerHTML = '<span class="text-danger">❌ Echo not available</span>';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

// Auto-refresh notifications every 30 seconds
setInterval(loadNotifications, 30000);
</script>
@endsection
