<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Tickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $rooms = $this->getRoomsForUser($user);
        
        return response()->json([
            'rooms' => $rooms,
            'user_type' => $this->getUserType($user)
        ]);
    }

    public function createCustomerSupportRoom(Request $request)
    {
        Log::info('=== CREATE CUSTOMER SUPPORT ROOM ===');
        Log::info('Request data:', $request->all());
        Log::info('Auth user:', ['id' => Auth::id(), 'name' => Auth::user()->name]);
        
        $request->validate([
            'ticket_id' => 'required|string',
            'customer_name' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        $ticketNumber = $request->ticket_id; // User input ticket number
        
        Log::info('Searching for ticket:', ['ticket_number' => $ticketNumber]);
        
        // Cari ticket berdasarkan ticket_number
        $ticket = Tickets::where('ticket_number', $ticketNumber)->first();
        
        Log::info('Ticket search result:', [
            'found' => !!$ticket,
            'ticket_data' => $ticket ? $ticket->toArray() : null
        ]);

        if (!$ticket) {
            Log::warning('Ticket not found', ['ticket_number' => $ticketNumber]);
            
            // Debug: Show available tickets
            $availableTickets = Tickets::take(5)->get(['id', 'ticket_number', 'subject']);
            Log::info('Available tickets (sample):', $availableTickets->toArray());
            
            return response()->json([
                'error' => 'Ticket not found with number: ' . $ticketNumber . '. Please check your ticket number and try again.'
            ], 404);
        }

        // Verify customer owns the ticket
        if ($ticket->user_id !== $user->id && !$user->hasRole(['admin', 'super-admin'])) {
            Log::warning('Unauthorized ticket access', [
                'ticket_user_id' => $ticket->user_id,
                'current_user_id' => $user->id
            ]);
            return response()->json(['error' => 'You can only create chat for your own tickets'], 403);
        }

        // Check if room already exists
        $existingRoom = ChatRoom::where('ticket_id', $ticket->id)
            ->where('type', 'customer_support')
            ->first();

        Log::info('Existing room check:', ['found' => !!$existingRoom]);

        if ($existingRoom) {
            Log::info('Returning existing room:', ['room_id' => $existingRoom->id]);
            return response()->json([
                'room' => $existingRoom->load(['messages.sender', 'customer', 'ticket']),
                'message' => 'Joined existing support chat'
            ]);
        }

        // Create new room
        Log::info('Creating new room with data:', [
            'ticket_id' => $ticket->id,
            'customer_id' => $user->id,
            'ticket_number' => $ticket->ticket_number
        ]);

        try {
            $room = ChatRoom::create([
                'name' => "Support for Ticket #{$ticket->ticket_number} - {$request->customer_name}",
                'type' => 'customer_support',
                'customer_id' => $user->id,
                'ticket_id' => $ticket->id, // Integer ID dari database
                'status' => 'active',
                'last_activity' => now()
            ]);

            Log::info('Room created successfully:', ['room_id' => $room->id]);

            // Send welcome message
            ChatMessage::create([
                'chat_room_id' => $room->id,
                'sender_id' => $user->id,
                'message' => "Hello! I need support for Ticket #{$ticket->ticket_number}. My name is {$request->customer_name}.",
                'sender_type' => 'customer'
            ]);

            Log::info('Welcome message sent');

            return response()->json([
                'room' => $room->load(['messages.sender', 'customer', 'ticket']),
                'message' => 'Support chat created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating room:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to create chat room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createTechnicianRoom(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('technician')) {
            return response()->json(['error' => 'Only technicians can create technician rooms'], 403);
        }

        // Check if technician already has an active room with admin
        $existingRoom = ChatRoom::where('technician_id', $user->id)
            ->where('type', 'technician_admin')
            ->where('status', 'active')
            ->first();

        if ($existingRoom) {
            return response()->json([
                'room' => $existingRoom->load(['messages.sender', 'technician']),
                'message' => 'Joined existing technician chat'
            ]);
        }

        // Create new technician-admin room
        $room = ChatRoom::create([
            'name' => "Technician Chat - {$user->name}",
            'type' => 'technician_admin',
            'technician_id' => $user->id,
            'status' => 'active',
            'last_activity' => now()
        ]);

        // Send welcome message
        ChatMessage::create([
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => "Hello Admin! This is {$user->name}, technician. I'm ready to report on my work progress.",
            'sender_type' => 'technician'
        ]);

        return response()->json([
            'room' => $room->load(['messages.sender', 'technician']),
            'message' => 'Technician chat created successfully'
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:chat_rooms,id',
            'message' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $room = ChatRoom::findOrFail($request->room_id);

        if (!$room->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized access to chat room'], 403);
        }

        $senderType = $this->getUserType($user);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'sender_type' => $senderType
        ]);

        // Update room last activity
        $room->update(['last_activity' => now()]);

        // Load sender relationship
        $message->load('sender');

        // Broadcast message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'success' => true
        ]);
    }

    public function getRoomMessages($roomId)
    {
        $user = Auth::user();
        $room = ChatRoom::findOrFail($roomId);

        if (!$room->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized access to chat room'], 403);
        }

        $messages = $room->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read for current user
        $room->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'messages' => $messages,
            'room' => $room->load(['customer', 'technician', 'ticket'])
        ]);
    }

    public function getUserChats()
    {
        $user = Auth::user();
        $rooms = $this->getRoomsForUser($user);

        return response()->json([
            'rooms' => $rooms,
            'user_type' => $this->getUserType($user)
        ]);
    }

    public function getAllUsersForAdmin()
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Only super admin can access this'], 403);
        }

        // Get all technicians
        $technicians = User::role('technician')->get(['id', 'name', 'email']);
        
        // Get all customers with active support chats
        $customers = User::whereHas('customerChats', function($query) {
            $query->where('status', 'active');
        })->get(['id', 'name', 'email']);

        return response()->json([
            'technicians' => $technicians,
            'customers' => $customers
        ]);
    }

    private function getRoomsForUser($user)
    {
        $query = ChatRoom::with(['messages' => function($query) {
            $query->latest()->limit(1);
        }, 'customer', 'technician', 'ticket']);

        if ($user->isSuperAdmin()) {
            // Super admin can see all rooms
            return $query->orderBy('last_activity', 'desc')->get();
        } elseif ($user->isAdmin()) {
            // Admin can see customer support rooms
            return $query->where('type', 'customer_support')
                ->orderBy('last_activity', 'desc')->get();
        } elseif ($user->hasRole('technician')) {
            // Technician can see their own rooms
            return $query->where('technician_id', $user->id)
                ->orderBy('last_activity', 'desc')->get();
        } else {
            // Customer can see their own support rooms
            return $query->where('customer_id', $user->id)
                ->orderBy('last_activity', 'desc')->get();
        }
    }

    private function getUserType($user)
    {
        if ($user->isSuperAdmin()) {
            return 'super_admin';
        } elseif ($user->isAdmin()) {
            return 'admin';
        } elseif ($user->hasRole('technician')) {
            return 'technician';
        } else {
            return 'customer';
        }
    }
}
