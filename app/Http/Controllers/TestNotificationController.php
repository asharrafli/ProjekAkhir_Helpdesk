<?php

namespace App\Http\Controllers;

use App\Events\TicketCreated;
use App\Events\TestEvent;
use App\Models\Tickets;
use App\Models\User;
use App\Notifications\TicketCreated as TicketCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Test broadcasting and notifications
     */
    public function testBroadcast(Request $request)
    {
        try {
            // Get the first ticket for testing
            $ticket = Tickets::with(['user', 'category'])->first();
            
            if (!$ticket) {
                return response()->json([
                    'error' => 'No tickets found. Please create a ticket first.'
                ], 404);
            }

            Log::info('Testing broadcast with ticket:', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number
            ]);

            // Test 1: Direct Event Broadcasting
            Log::info('Dispatching TicketCreated event...');
            event(new TicketCreated($ticket));
            
            // Test 2: Direct Notification Broadcasting
            Log::info('Sending notification to authenticated user...');
            $user = auth()->user();
            $user->notify(new TicketCreatedNotification($ticket));

            // Test 3: Broadcast to all users with technician role
            $technicians = User::role('technician')->get();
            Log::info('Sending notifications to technicians:', [
                'count' => $technicians->count()
            ]);
            
            foreach ($technicians as $technician) {
                $technician->notify(new TicketCreatedNotification($ticket));
            }

            return response()->json([
                'success' => true,
                'message' => 'Test broadcast completed successfully',
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title ?? $ticket->title_ticket,
                ],
                'sent_to' => [
                    'current_user' => $user->email,
                    'technicians' => $technicians->pluck('email')->toArray()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Test broadcast failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Pusher connection by firing a simple test event
     */
    public function testPusher()
    {
        try {
            Log::info('Starting Pusher connection test...');

            // Check if broadcasting is properly configured
            $broadcastDriver = config('broadcasting.default');
            if ($broadcastDriver !== 'pusher') {
                throw new \Exception("Broadcasting driver is '{$broadcastDriver}', expected 'pusher'");
            }

            // Test by creating a test event
            $testData = [
                'message' => 'Pusher connection test successful!',
                'timestamp' => now()->toDateTimeString(),
                'user' => auth()->user()->email
            ];

            // Fire the test event
            event(new TestEvent($testData));

            Log::info('Pusher test event dispatched successfully', $testData);

            return response()->json([
                'success' => true,
                'message' => 'Pusher test completed - check your Pusher dashboard for the test-channel event',
                'test_data' => $testData,
                'broadcast_driver' => $broadcastDriver
            ]);

        } catch (\Exception $e) {
            Log::error('Pusher test failed:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Pusher test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broadcasting status
     */
    public function status()
    {
        $config = [
            'broadcast_driver' => config('broadcasting.default'),
            'queue_connection' => config('queue.default'),
            'pusher_app_id' => config('broadcasting.connections.pusher.app_id'),
            'pusher_key' => config('broadcasting.connections.pusher.key'),
            'pusher_cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'pusher_encrypted' => config('broadcasting.connections.pusher.options.useTLS'),
        ];

        // Check if we can create a broadcasting instance
        $pusher_status = 'unknown';
        try {
            $broadcaster = app('Illuminate\Broadcasting\BroadcastManager');
            $pusherDriver = $broadcaster->driver('pusher');
            $pusher_status = 'broadcasting driver loaded';
        } catch (\Exception $e) {
            $pusher_status = 'error: ' . $e->getMessage();
        }

        // Check notifications count
        $user = auth()->user();
        $totalNotifications = $user->notifications()->count();
        $unreadNotifications = $user->unreadNotifications()->count();

        return response()->json([
            'broadcasting_config' => $config,
            'pusher_status' => $pusher_status,
            'current_user' => auth()->user()->email,
            'notifications_count' => $totalNotifications,
            'unread_count' => $unreadNotifications,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Create a test notification directly in database
     */
    public function createTestNotification()
    {
        try {
            $user = auth()->user();
            
            // Get or create a ticket for testing
            $ticket = \App\Models\Tickets::first();
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tickets found. Please create a ticket first to test notifications.'
                ], 404);
            }
            
            // Create a test notification directly
            $user->notify(new \App\Notifications\TicketCreated($ticket));

            Log::info('Test notification created for user:', [
                'user_id' => $user->id,
                'ticket_id' => $ticket->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created and saved to database',
                'ticket_number' => $ticket->ticket_number,
                'total_notifications' => $user->notifications()->count(),
                'unread_notifications' => $user->unreadNotifications()->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create test notification:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}