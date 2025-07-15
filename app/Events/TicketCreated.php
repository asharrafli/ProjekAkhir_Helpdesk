<?php

namespace App\Events;

use App\Models\Tickets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TicketCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    public function __construct(Tickets $ticket)
    {
        $this->ticket = $ticket;
        Log::info('🚀 TicketCreated event constructed', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number
        ]);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('tickets'),
        ];
        
        Log::info('📡 TicketCreated broadcasting on channels', [
            'channels' => ['tickets'],
            'ticket_id' => $this->ticket->id
        ]);
        
        return $channels;
    }

    public function broadcastWith(): array
    {
        $data = [
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'title' => $this->ticket->title ?? $this->ticket->title_ticket,
                'status' => $this->ticket->status,
                'priority' => $this->ticket->priority,
                'category' => $this->ticket->category?->name ?? 'Uncategorized',
                'created_by' => $this->ticket->user?->name ?? 'Unknown User',
                'created_at' => $this->ticket->created_at->toDateTimeString(),
            ],
            'message' => "New ticket #{$this->ticket->ticket_number} has been created",
            'type' => 'ticket_created',
        ];
        
        Log::info('✅ TicketCreated broadcastWith data prepared', $data);
        
        return $data;
    }

    public function broadcastAs(): string
    {
        Log::info('🔊 TicketCreated broadcastAs called - event name: ticket.created');
        return 'ticket.created';
    }
}