<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tickets;
use App\Models\Categories;
use App\Models\Subcategories;
use App\Events\TicketCreated;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Notifications\TicketCreated as TicketCreatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TestTicketController extends Controller
{
    public function createTestTicket()
    {
        try {
            $user = Auth::user();
            
            // Get or create test category
            $category = TicketCategory::firstOrCreate([
                'name' => 'Test Category'
            ]);
            
            $subcategory = TicketSubcategory::firstOrCreate([
                'name' => 'Test Subcategory',
                'category_id' => $category->id
            ]);
            
            // Create test ticket
            $ticket = Tickets::create([
                'ticket_number' => 'TEST-' . time(),
                'title_ticket' => 'Test Ticket for Notification',
                'description' => 'This is a test ticket to verify notifications are working',
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'priority' => 'medium',
                'status' => 'open',
                'user_id' => $user->id,
            ]);
            
            // Dispatch event
            event(new TicketCreated($ticket));
            
            // Send notifications to admins
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketCreatedNotification($ticket));
            }
            
            Log::info('✅ Test ticket created and notifications sent', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test ticket created and notifications sent',
                'ticket' => [
                    'id' => $ticket->id,
                    'number' => $ticket->ticket_number,
                    'title' => $ticket->title_ticket
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Test ticket creation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
