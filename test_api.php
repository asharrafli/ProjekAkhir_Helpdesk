<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create request
$request = Illuminate\Http\Request::create('/manager/dashboard/chart-data?type=ticket_trends&period=week', 'GET');

// Create controller instance
$controller = new App\Http\Controllers\Admin\ManagerDashboardController();

try {
    // Test the method
    $response = $controller->getChartData($request);
    
    if ($response instanceof Illuminate\Http\JsonResponse) {
        echo "API Response Status: " . $response->getStatusCode() . "\n";
        echo "API Response Data: " . $response->getContent() . "\n";
    } else {
        echo "Response: " . print_r($response, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
