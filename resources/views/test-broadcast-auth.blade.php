@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Broadcasting Auth Test</div>
                <div class="card-body">
                    <p>User ID: <strong>{{ Auth::id() }}</strong></p>
                    <p>Channel: <strong>private-App.Models.User.{{ Auth::id() }}</strong></p>
                    
                    <button class="btn btn-primary" onclick="testPrivateChannel()">Test Private Channel</button>
                    <button class="btn btn-success" onclick="sendTestNotification()">Send Test Notification</button>
                    
                    <div id="results" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function logResult(message, type = 'info') {
    const colors = { info: 'blue', success: 'green', error: 'red' };
    document.getElementById('results').innerHTML += `<div style="color: ${colors[type]}; margin: 5px 0;">[${new Date().toLocaleTimeString()}] ${message}</div>`;
}

function testPrivateChannel() {
    const userId = {{ Auth::id() }};
    logResult('Testing private channel subscription...', 'info');
    
    try {
        const channel = window.Echo.private(`App.Models.User.${userId}`);
        
        channel.subscribed(() => {
            logResult('✅ Successfully subscribed to private channel!', 'success');
        });
        
        channel.error((error) => {
            logResult('❌ Failed to subscribe: ' + JSON.stringify(error), 'error');
        });
        
        channel.notification((notification) => {
            logResult('📨 Received notification: ' + JSON.stringify(notification), 'success');
        });
        
    } catch (error) {
        logResult('❌ Error setting up channel: ' + error.message, 'error');
    }
}

function sendTestNotification() {
    logResult('Sending test notification...', 'info');
    
    fetch('/test/broadcast', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            logResult('✅ Test notification sent successfully!', 'success');
        } else {
            logResult('❌ Failed to send: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        logResult('❌ Network error: ' + error.message, 'error');
    });
}

// Auto-test on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        testPrivateChannel();
    }, 1000);
});
</script>
@endsection