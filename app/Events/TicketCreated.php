<?php

namespace App\Events;

use App\Models\Tickets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tickets $ticket
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tickets'),
            new PrivateChannel('user.' . $this->ticket->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'title' => $this->ticket->title ?? $this->ticket->title_ticket,
                'priority' => $this->ticket->priority,
                'status' => $this->ticket->status,
                'category' => $this->ticket->category->name,
                'created_by' => $this->ticket->user->name,
                'created_at' => $this->ticket->created_at->toDateTimeString(),
            ],
            'message' => "New ticket #{$this->ticket->ticket_number} has been created",
            'type' => 'ticket_created',
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.created';
    }
}