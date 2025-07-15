<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;
use App\Models\Tickets;

class TicketCreated extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $ticket;

    public function __construct(Tickets $ticket)
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
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => 'New Ticket Created',
            'message' => "Ticket #{$this->ticket->ticket_number} has been created by {$this->ticket->user->name}",
            'type' => 'ticket_created',
            'created_at' => now(),
        ];
    }

    // Update toBroadcast method untuk broadcast ke channel public
    public function toBroadcast($notifiable)
    {
        $data = [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => 'New Ticket Created',
            'message' => "Ticket #{$this->ticket->ticket_number} has been created by {$this->ticket->user->name}",
            'type' => 'ticket_created',
            'created_at' => now()->toISOString(),
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'number' => $this->ticket->ticket_number,
                'title' => $this->ticket->title_ticket,
                'title_ticket' => $this->ticket->title_ticket,
                'priority' => $this->ticket->priority,
                'status' => $this->ticket->status,
                'created_by' => $this->ticket->user->name,
                'user' => [
                    'id' => $this->ticket->user->id,
                    'name' => $this->ticket->user->name
                ]
            ]
        ];
        
        Log::info('📡 Broadcasting notification data', $data);
        
        return new BroadcastMessage($data);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        $channels = [
            new Channel('tickets'),                    // Public - semua user dapat akses
            new Channel('notifications.global'),      // Global - semua user dapat akses
        ];

        Log::info('📡 Broadcasting ticket notification to all users', [
            'channels_count' => count($channels),
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'creator' => $this->ticket->user->name ?? 'Unknown'
        ]);

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'ticket.created';
    }
}