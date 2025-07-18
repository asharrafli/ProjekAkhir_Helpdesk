<?php

// Test API chart endpoints
$baseUrl = 'http://projekakhir-final.test';

// Test different chart types
$chartTypes = [
    'ticket_trends',
    'priority_distribution',
    'technician_performance',
    'category_distribution',
    'resolution_time'
];

foreach ($chartTypes as $type) {
    echo "Testing chart type: $type\n";
    
    // Correct URL according to routes
    $url = "$baseUrl/manager/dashboard/chart-data?type=$type&period=week";
    echo "URL: $url\n";
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        echo "❌ Failed to get response for $type\n";
        continue;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Invalid JSON response for $type\n";
        echo "Raw response: " . substr($response, 0, 200) . "...\n";
        continue;
    }
    
    if (isset($data['error'])) {
        echo "❌ Error response for $type: " . $data['error'] . "\n";
        continue;
    }
    
    if (isset($data['labels']) && isset($data['datasets'])) {
        echo "✅ Valid response for $type\n";
        echo "   Labels: " . count($data['labels']) . " items\n";
        echo "   Datasets: " . count($data['datasets']) . " items\n";
    } else {
        echo "❌ Invalid data structure for $type\n";
        echo "   Keys: " . implode(', ', array_keys($data)) . "\n";
    }
    
    echo "\n";
}

echo "Test completed!\n";
