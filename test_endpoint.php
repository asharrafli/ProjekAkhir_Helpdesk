<?php
// Test script to check API endpoint
echo "Testing API endpoint...\n";

// Test different URLs
$urls = [
    'http://projekakhir-final.test/manager/dashboard/chart-data?type=ticket_trends&period=week'
];

foreach ($urls as $url) {
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to connect\n";
    } else {
        $http_response_header = $http_response_header ?? [];
        $status_line = $http_response_header[0] ?? 'Unknown';
        echo "✅ Status: $status_line\n";
        
        // Show first 200 characters of response
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
    echo "---\n";
}
?>
