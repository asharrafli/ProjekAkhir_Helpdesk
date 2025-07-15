<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Log;

// For ticket creator
class TicketCreatedConfirmation extends Notification
{
    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['database','broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Ticket Created Successfully',
            'message' => "Your ticket #{$this->ticket->ticket_number} has been created and is being processed",
            'type' => 'success',
            'ticket_id' => $this->ticket->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Ticket Created Successfully',
            'message' => "Your ticket #{$this->ticket->ticket_number} has been created and is being processed",
            'type' => 'success',
            'ticket_id' => $this->ticket->id,
            'created_at' => now(),
        ]);
    }

    public function broadcastOn()
    {
        $channels = [
            new Channel('notifications.global'),
            new PrivateChannel('notifications.' . $this->ticket->user_id),
        ];

        Log::info('📡 Broadcasting ticket confirmation', [
            'channels_count' => count($channels),
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->ticket->user_id
        ]);

        return $channels;
    }

    public function broadcastAs()
    {
        return 'ticket.created.confirmation';
    }
}