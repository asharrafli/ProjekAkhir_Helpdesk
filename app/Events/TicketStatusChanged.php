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
use Illuminate\Support\Facades\Auth;

class TicketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tickets $ticket,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('tickets'),
        ];

        // Add private channels for involved users
        if ($this->ticket->assigned_to) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->assigned_to);
        }

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
                'old_status' => $this->oldStatus,
                'updated_by' => Auth::user()->name ?? 'System',
            ],
            'message' => "Ticket #{$this->ticket->ticket_number} status changed from {$this->oldStatus} to {$this->newStatus}",
            'type' => 'ticket_status_changed',
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.status.changed';
    }
}