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

class TicketAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tickets $ticket
    ) {}

    public function broadcastOn(): array
    {
         $channels = [
            new Channel('tickets'),
        ];

        // Add private channel for assigned technician
        if ($this->ticket->assigned_to) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->assigned_to);
        }

        // Add private channel for ticket creator
        if ($this->ticket->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->user_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'title' => $this->ticket->title_ticket,
                'priority' => $this->ticket->priority,
                'status' => $this->ticket->status,
                'assigned_to' => $this->ticket->assignedTo->name,
                'assigned_by' => request()->user()->name ?? 'System',
            ],
            'message' => "Ticket #{$this->ticket->ticket_number} has been assigned to {$this->ticket->assignedTo->name}",
            'type' => 'ticket_assigned',
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.assigned';
    }
}