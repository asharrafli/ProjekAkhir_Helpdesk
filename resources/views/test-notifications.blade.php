@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-broadcast"></i> Real-time Notifications Debug Panel</h4>
                    <small class="text-muted">Use this panel to test and debug real-time notifications</small>
                </div>
                <div class="card-body">
                    <!-- Status Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Broadcasting Status</h6>
                                </div>
                                <div class="card-body">
                                    <div id="status-info">Loading...</div>
                                    <button class="btn btn-sm btn-info mt-2" onclick="loadStatus()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh Status
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-wifi"></i> Connection Status</h6>
                                </div>
                                <div class="card-body">
                                    <div id="connection-status">
                                        <span class="badge bg-warning">Checking...</span>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        User ID: <strong>{{ Auth::id() }}</strong><br>
                                        Email: <strong>{{ Auth::user()->email }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Buttons -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bi bi-play-circle"></i> Test Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-success" onclick="testBroadcast()">
                                            <i class="bi bi-broadcast"></i> Test Ticket Notification
                                        </button>
                                        <button class="btn btn-warning" onclick="testPusher()">
                                            <i class="bi bi-wifi"></i> Test Pusher Connection
                                        </button>
                                        <button class="btn btn-info" onclick="clearLogs()">
                                            <i class="bi bi-trash"></i> Clear Console
                                        </button>
                                    </div>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Instructions:</strong> Open your browser's developer console (F12) to see detailed logging output.
                                        Test notifications will appear as toasts and in the notification dropdown.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Console Log -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-dark">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0"><i class="bi bi-terminal"></i> Test Results</h6>
                                </div>
                                <div class="card-body">
                                    <div id="test-results" class="bg-light p-3 rounded" style="min-height: 200px; font-family: monospace; font-size: 12px; overflow-y: auto;">
                                        <div class="text-muted">Test results will appear here...</div>
                                    </div>
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
let testResults = document.getElementById('test-results');

function logResult(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const colors = {
        info: '#17a2b8',
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107'
    };
    
    testResults.innerHTML += `
        <div style="color: ${colors[type]}; margin-bottom: 5px;">
            [${timestamp}] ${message}
        </div>
    `;
    testResults.scrollTop = testResults.scrollHeight;
}

function clearLogs() {
    testResults.innerHTML = '<div class="text-muted">Logs cleared...</div>';
    console.clear();
}

function updateConnectionStatus(status, message) {
    const statusEl = document.getElementById('connection-status');
    const badges = {
        connected: 'bg-success',
        connecting: 'bg-warning',
        disconnected: 'bg-danger',
        error: 'bg-danger'
    };
    
    statusEl.innerHTML = `
        <span class="badge ${badges[status] || 'bg-secondary'}">${status.toUpperCase()}</span>
        <div class="small text-muted mt-1">${message}</div>
    `;
}

async function loadStatus() {
    try {
        logResult('Loading broadcasting status...', 'info');
        const response = await fetch('/test/status');
        const data = await response.json();
        
        document.getElementById('status-info').innerHTML = `
            <table class="table table-sm">
                <tr><td><strong>Broadcast Driver:</strong></td><td>${data.broadcasting_config.broadcast_driver}</td></tr>
                <tr><td><strong>Queue Connection:</strong></td><td>${data.broadcasting_config.queue_connection}</td></tr>
                <tr><td><strong>Pusher App ID:</strong></td><td>${data.broadcasting_config.pusher_app_id ? 'Set' : 'Not Set'}</td></tr>
                <tr><td><strong>Pusher Key:</strong></td><td>${data.broadcasting_config.pusher_key ? 'Set' : 'Not Set'}</td></tr>
                <tr><td><strong>Pusher Cluster:</strong></td><td>${data.broadcasting_config.pusher_cluster}</td></tr>
                <tr><td><strong>Pusher Status:</strong></td><td>${data.pusher_status}</td></tr>
            </table>
        `;
        
        logResult('Status loaded successfully', 'success');
    } catch (error) {
        logResult('Error loading status: ' + error.message, 'error');
    }
}

async function testBroadcast() {
    try {
        logResult('Testing ticket notification broadcast...', 'info');
        const response = await fetch('/test/broadcast', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            logResult('✅ Ticket notification test completed successfully', 'success');
            logResult(`Ticket: ${data.ticket.ticket_number} - ${data.ticket.title}`, 'info');
            logResult(`Sent to: ${data.sent_to.current_user}`, 'info');
            if (data.sent_to.technicians.length > 0) {
                logResult(`Also sent to technicians: ${data.sent_to.technicians.join(', ')}`, 'info');
            }
        } else {
            logResult('❌ Test failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        logResult('❌ Network error: ' + error.message, 'error');
    }
}

async function testPusher() {
    try {
        logResult('Testing Pusher connection...', 'info');
        const response = await fetch('/test/pusher', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            logResult('✅ Pusher connection test successful', 'success');
            logResult('Check the "test-channel" in Pusher Debug Console', 'info');
        } else {
            logResult('❌ Pusher test failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        logResult('❌ Network error: ' + error.message, 'error');
    }
}

// Initialize connection monitoring
document.addEventListener('DOMContentLoaded', function() {
    loadStatus();
    
    // Monitor Echo connection status
    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        const pusher = window.Echo.connector.pusher;
        
        pusher.connection.bind('connected', () => {
            updateConnectionStatus('connected', 'Successfully connected to Pusher');
            logResult('✅ Pusher connected', 'success');
        });
        
        pusher.connection.bind('connecting', () => {
            updateConnectionStatus('connecting', 'Connecting to Pusher...');
            logResult('🔄 Connecting to Pusher...', 'warning');
        });
        
        pusher.connection.bind('disconnected', () => {
            updateConnectionStatus('disconnected', 'Disconnected from Pusher');
            logResult('❌ Pusher disconnected', 'error');
        });
        
        pusher.connection.bind('error', (error) => {
            updateConnectionStatus('error', 'Connection error: ' + error.error);
            logResult('❌ Pusher error: ' + JSON.stringify(error), 'error');
        });
        
        // Initial status
        updateConnectionStatus(pusher.connection.state, 'Current connection state');
    } else {
        updateConnectionStatus('error', 'Echo/Pusher not properly initialized');
        logResult('❌ Echo/Pusher not found', 'error');
    }
});
</script>
@endsection