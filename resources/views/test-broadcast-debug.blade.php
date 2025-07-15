@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>🔧 Broadcasting Debug Dashboard</h4>
                    <p>Test and debug real-time notifications</p>
                </div>
                <div class="card-body">
                    <!-- Connection Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    📡 Connection Status
                                </div>
                                <div class="card-body">
                                    <div id="connection-status" class="alert alert-warning">
                                        🔄 Checking connection...
                                    </div>
                                    <div id="echo-status" class="small text-muted">
                                        Echo: Not initialized
                                    </div>
                                    <div id="pusher-status" class="small text-muted">
                                        Pusher: Not connected
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    🎯 Channel Subscriptions
                                </div>
                                <div class="card-body">
                                    <div id="channel-status">
                                        <div class="small text-muted">No channels subscribed yet</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Buttons -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>🧪 Test Actions</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary" onclick="testBroadcast()">
                                    🔥 Test Broadcasting
                                </button>
                                <button type="button" class="btn btn-info" onclick="testPusherConnection()">
                                    🔗 Test Pusher
                                </button>
                                <button type="button" class="btn btn-warning" onclick="debugConfig()">
                                    ⚙️ Debug Config
                                </button>
                                <button type="button" class="btn btn-success" onclick="createTicket()">
                                    🎫 Create Test Ticket
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Logs -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    📋 Real-time Logs
                                    <button class="btn btn-sm btn-secondary float-right" onclick="clearLogs()">Clear</button>
                                </div>
                                <div class="card-body">
                                    <div id="debug-logs" style="height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #f8f9fa; padding: 10px;">
                                        <!-- Logs will appear here -->
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
let originalConsoleLog = console.log;
let originalConsoleError = console.error;
let originalConsoleWarn = console.warn;

// Override console methods to show in our logs
console.log = function(...args) {
    originalConsoleLog.apply(console, args);
    addToDebugLogs('LOG', args.join(' '));
};

console.error = function(...args) {
    originalConsoleError.apply(console, args);
    addToDebugLogs('ERROR', args.join(' '), 'danger');
};

console.warn = function(...args) {
    originalConsoleWarn.apply(console, args);
    addToDebugLogs('WARN', args.join(' '), 'warning');
};

function addToDebugLogs(type, message, alertType = 'info') {
    const logsContainer = document.getElementById('debug-logs');
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = document.createElement('div');
    logEntry.className = `alert alert-${alertType} py-1 px-2 mb-1`;
    logEntry.innerHTML = `<small><strong>[${timestamp}] ${type}:</strong> ${message}</small>`;
    logsContainer.appendChild(logEntry);
    logsContainer.scrollTop = logsContainer.scrollHeight;
}

function clearLogs() {
    document.getElementById('debug-logs').innerHTML = '';
}

// Test functions
async function testBroadcast() {
    addToDebugLogs('ACTION', '🔥 Testing broadcast...');
    try {
        const response = await fetch('/test/broadcast-debug');
        const data = await response.json();
        addToDebugLogs('SUCCESS', `Broadcast test: ${data.message}`, 'success');
    } catch (error) {
        addToDebugLogs('ERROR', `Broadcast test failed: ${error.message}`, 'danger');
    }
}

async function testPusherConnection() {
    addToDebugLogs('ACTION', '🔗 Testing Pusher connection...');
    try {
        const response = await fetch('/test/pusher-test');
        const data = await response.json();
        addToDebugLogs('SUCCESS', `Pusher test: ${data.message}`, 'success');
    } catch (error) {
        addToDebugLogs('ERROR', `Pusher test failed: ${error.message}`, 'danger');
    }
}

async function debugConfig() {
    addToDebugLogs('ACTION', '⚙️ Getting debug config...');
    try {
        const response = await fetch('/test/config-debug');
        const data = await response.json();
        addToDebugLogs('INFO', `Config: ${JSON.stringify(data.config, null, 2)}`, 'info');
    } catch (error) {
        addToDebugLogs('ERROR', `Config debug failed: ${error.message}`, 'danger');
    }
}

async function createTicket() {
    addToDebugLogs('ACTION', '🎫 Creating test ticket...');
    try {
        const response = await fetch('/tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                title_ticket: 'Test Ticket for Broadcasting',
                description: 'This is a test ticket created from debug dashboard',
                priority: 'medium',
                category_id: 1
            })
        });
        
        if (response.ok) {
            addToDebugLogs('SUCCESS', 'Test ticket created! Check for notifications.', 'success');
        } else {
            addToDebugLogs('ERROR', 'Failed to create ticket', 'danger');
        }
    } catch (error) {
        addToDebugLogs('ERROR', `Ticket creation failed: ${error.message}`, 'danger');
    }
}

// Check status on load
document.addEventListener('DOMContentLoaded', function() {
    addToDebugLogs('INIT', '🚀 Debug dashboard loaded');
    
    // Check Echo status
    setTimeout(() => {
        if (window.Echo) {
            addToDebugLogs('STATUS', '✅ Echo is initialized', 'success');
            document.getElementById('echo-status').textContent = 'Echo: ✅ Initialized';
            
            if (window.Echo.connector && window.Echo.connector.pusher) {
                const state = window.Echo.connector.pusher.connection.state;
                addToDebugLogs('STATUS', `📡 Pusher connection state: ${state}`, state === 'connected' ? 'success' : 'warning');
                document.getElementById('pusher-status').textContent = `Pusher: ${state}`;
            }
        } else {
            addToDebugLogs('STATUS', '❌ Echo not initialized', 'danger');
            document.getElementById('echo-status').textContent = 'Echo: ❌ Not initialized';
        }
    }, 1000);
});
</script>
@endsection