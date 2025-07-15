<!-- filepath: resources/views/test-notification.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Test Notification</h1>
    <div id="notifications"></div>
    <button onclick="testNotification()">Test Database Notification</button>
</div>

<script>
function testNotification() {
    fetch('/test-pusher')
        .then(response => response.json())
        .then(data => {
            console.log('Test result:', data);
            document.getElementById('notifications').innerHTML = 
                '<div class="alert alert-info">' + JSON.stringify(data) + '</div>';
        });
}

// Test Echo connection
if (typeof window.Echo !== 'undefined') {
    console.log('✅ Echo is available');
    
    // Test channel subscription
    window.Echo.channel('tickets')
        .listen('.ticket.created', (e) => {
            console.log('🎯 Received notification:', e);
            document.getElementById('notifications').innerHTML = 
                '<div class="alert alert-success">Notification received: ' + JSON.stringify(e) + '</div>';
        });
} else {
    console.error('❌ Echo is not available');
}
</script>
@endsection
