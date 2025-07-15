<!DOCTYPE html>
<html>
<head>
    <title>Broadcasting Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo@1.15.0/dist/echo.iife.js"></script>
</head>
<body>
    <h1>Broadcasting Test Page</h1>
    
    <button onclick="testBroadcast()">Test Broadcast</button>
    <button onclick="clearLog()">Clear Log</button>
    
    <div id="log" style="margin-top: 20px; background: #f5f5f5; padding: 10px; height: 300px; overflow-y: scroll;">
        <p>Log messages will appear here...</p>
    </div>

    <script>
        function log(message) {
            const logDiv = document.getElementById('log');
            const p = document.createElement('p');
            p.textContent = new Date().toLocaleTimeString() + ': ' + message;
            logDiv.appendChild(p);
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function clearLog() {
            document.getElementById('log').innerHTML = '<p>Log cleared...</p>';
        }

        // Setup Echo
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("VITE_PUSHER_APP_KEY") }}',
            cluster: '{{ env("VITE_PUSHER_APP_CLUSTER") }}',
            forceTLS: true
        });

        // Setup listeners
        window.Echo.channel('tickets')
            .listen('.ticket.created', (data) => {
                log('🎯 BROADCAST RECEIVED!');
                log('📋 Ticket: ' + data.ticket.ticket_number);
                log('💬 Message: ' + data.message);
                alert('🎉 Broadcast received! Ticket: ' + data.ticket.ticket_number);
            });

        log('✅ Echo listener setup complete');
        log('🚀 Ready to receive broadcasts');

        function testBroadcast() {
            log('🧪 Triggering broadcast test...');
            
            fetch('/test-simple-broadcast', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                log('✅ Broadcast triggered: ' + data.message);
                log('🎫 Ticket: ' + data.ticket.ticket_number);
            })
            .catch(error => {
                log('❌ Error: ' + error);
            });
        }
    </script>
</body>
</html>