<?php

namespace App\Http\Controllers;

use App\Events\TicketCreated;
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
     * Test Pusher connection
     */
    public function testPusher()
    {
        try {
            $pusher = app('pusher');
            
            // Test simple broadcast
            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Pusher connection test successful!',
                'timestamp' => now()->toDateTimeString(),
                'user' => auth()->user()->email
            ]);

            Log::info('Pusher test result:', ['result' => $result]);

            return response()->json([
                'success' => true,
                'message' => 'Pusher test completed',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Pusher test failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
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

        // Check if we can create a Pusher instance
        $pusher_status = 'unknown';
        try {
            $pusher = app('pusher');
            $pusher_status = 'connected';
        } catch (\Exception $e) {
            $pusher_status = 'error: ' . $e->getMessage();
        }

        return response()->json([
            'broadcasting_config' => $config,
            'pusher_status' => $pusher_status,
            'current_user' => auth()->user()->email,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}