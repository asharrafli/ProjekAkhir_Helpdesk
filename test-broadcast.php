<?php

// Simple test script to verify broadcasting configuration
require_once 'vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test Pusher configuration
$pusher_app_id = $_ENV['PUSHER_APP_ID'] ?? '';
$pusher_key = $_ENV['PUSHER_APP_KEY'] ?? '';
$pusher_secret = $_ENV['PUSHER_APP_SECRET'] ?? '';
$pusher_cluster = $_ENV['PUSHER_APP_CLUSTER'] ?? '';

echo "=== Broadcasting Configuration Test ===\n";
echo "BROADCAST_DRIVER: " . ($_ENV['BROADCAST_DRIVER'] ?? 'not set') . "\n";
echo "PUSHER_APP_ID: " . ($pusher_app_id ? 'set' : 'not set') . "\n";
echo "PUSHER_APP_KEY: " . ($pusher_key ? 'set' : 'not set') . "\n";
echo "PUSHER_APP_SECRET: " . ($pusher_secret ? 'set' : 'not set') . "\n";
echo "PUSHER_APP_CLUSTER: " . ($pusher_cluster ? $pusher_cluster : 'not set') . "\n";

if ($pusher_app_id && $pusher_key && $pusher_secret && $pusher_cluster) {
    echo "\n✅ All Pusher credentials are configured\n";
    
    // Try to create a Pusher instance
    try {
        $pusher = new Pusher\Pusher($pusher_key, $pusher_secret, $pusher_app_id, [
            'cluster' => $pusher_cluster,
            'useTLS' => true,
        ]);
        
        echo "✅ Pusher instance created successfully\n";
        
        // Test a simple broadcast
        $result = $pusher->trigger('test-channel', 'test-event', [
            'message' => 'Broadcasting test successful!',
            'timestamp' => time()
        ]);
        
        if ($result) {
            echo "✅ Test broadcast sent successfully\n";
        } else {
            echo "❌ Test broadcast failed\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creating Pusher instance: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n❌ Missing required Pusher credentials\n";
}

echo "\n=== Frontend Configuration Check ===\n";
echo "Check if these environment variables are available to Vite:\n";
echo "VITE_PUSHER_APP_KEY: " . ($_ENV['VITE_PUSHER_APP_KEY'] ?? 'not set') . "\n";
echo "VITE_PUSHER_APP_CLUSTER: " . ($_ENV['VITE_PUSHER_APP_CLUSTER'] ?? 'not set') . "\n";

// Check if Laravel Echo is properly configured
$bootstrap_js = file_get_contents('resources/js/bootstrap.js');
if (strpos($bootstrap_js, 'import Echo from \'laravel-echo\'') !== false) {
    echo "✅ Laravel Echo is imported in bootstrap.js\n";
} else {
    echo "❌ Laravel Echo import not found in bootstrap.js\n";
}

if (strpos($bootstrap_js, 'window.Echo = new Echo') !== false) {
    echo "✅ Echo instance is being created\n";
} else {
    echo "❌ Echo instance creation not found\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Make sure the queue worker is running: php artisan queue:work\n";
echo "2. Check browser console for any WebSocket connection errors\n";
echo "3. Verify Pusher credentials are valid and cluster is correct\n";
echo "4. Test with Pusher Debug Console to see if events are being sent\n";