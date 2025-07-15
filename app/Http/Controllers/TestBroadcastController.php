<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\TicketCreated;
use App\Models\Tickets;
use App\Models\User;
use App\Notifications\TicketCreated as TicketCreatedNotification;

class TestBroadcastController extends Controller
{
    public function testBroadcast()
    {
        try {
            Log::info('🧪 Test broadcast starting...');
            
            // Get atau create dummy ticket
            $ticket = Tickets::latest()->first();
            if (!$ticket) {
                $ticket = new Tickets();
                $ticket->ticket_number = 'TEST-' . date('Ymd') . '-' . rand(1000, 9999);
                $ticket->title_ticket = 'Test Ticket for Broadcasting';
                $ticket->description = 'This is a test ticket for broadcasting functionality';
                $ticket->priority = 'medium';
                $ticket->status = 'open';
                $ticket->user_id = auth()->id() ?? 1;
                $ticket->save();
            }
            
            // Test 1: Manual event firing
            Log::info('🔥 Firing TicketCreated event manually...');
            event(new TicketCreated($ticket));
            
            // Test 2: Test notification
            Log::info('🔔 Sending notification to current user...');
            $user = auth()->user() ?? User::first();
            $user->notify(new TicketCreatedNotification($ticket));
            
            Log::info('✅ Test broadcast completed successfully', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test broadcast completed successfully',
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title_ticket
                ],
                'logs' => 'Check Laravel logs for detailed broadcasting information'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Test broadcast failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Test broadcast failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testPusherConnection()
    {
        try {
            Log::info('🔗 Testing Pusher connection...');
            
            $pusher = new \Pusher\Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Test message from Laravel',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            Log::info('✅ Pusher test completed', ['result' => $result]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pusher connection test completed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Pusher connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Pusher connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function debugBroadcastConfig()
    {
        $config = [
            'broadcast_driver' => config('broadcasting.default'),
            'pusher_app_id' => config('broadcasting.connections.pusher.app_id'),
            'pusher_key' => config('broadcasting.connections.pusher.key'),
            'pusher_cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'queue_connection' => config('queue.default'),
        ];
        
        Log::info('📋 Broadcasting configuration', $config);
        
        return response()->json([
            'success' => true,
            'config' => $config
        ]);
    }
}