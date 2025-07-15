<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Tickets;
use App\Notifications\TicketCreated;
use App\Notifications\TicketCreatedConfirmation;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Notifications...\n";

try {
    // Get first user and ticket for testing
    $user = User::first();
    $ticket = Tickets::first();
    
    if (!$user || !$ticket) {
        echo "❌ No user or ticket found in database\n";
        exit;
    }
    
    echo "✅ Found user: {$user->name} (ID: {$user->id})\n";
    echo "✅ Found ticket: {$ticket->ticket_number} (ID: {$ticket->id})\n";
    
    // Test TicketCreated notification
    echo "\n📧 Testing TicketCreated notification...\n";
    $user->notify(new TicketCreated($ticket));
    echo "✅ TicketCreated notification sent\n";
    
    // Test TicketCreatedConfirmation notification
    echo "\n📧 Testing TicketCreatedConfirmation notification...\n";
    $user->notify(new TicketCreatedConfirmation($ticket));
    echo "✅ TicketCreatedConfirmation notification sent\n";
    
    echo "\n🎉 All notifications sent successfully!\n";
    echo "Check your browser and queue worker logs.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
