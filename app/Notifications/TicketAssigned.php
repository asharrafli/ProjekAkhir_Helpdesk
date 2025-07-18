<?php

namespace App\Notifications;

use App\Models\Tickets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tickets $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('A ticket has been assigned to you.')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title_ticket)
                    ->line('Priority: ' . ucfirst($this->ticket->priority))
                    ->action('View Ticket', route('tickets.show', $this->ticket))
                    ->line('Please review and take action as needed.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title_ticket,
            'priority' => $this->ticket->priority,
            'assigned_by' => Auth::user()->name ?? 'System',
            'message' => "Ticket #{$this->ticket->ticket_number} has been assigned to you",
            'type' => 'ticket_assigned',
            'url' => route('tickets.show', $this->ticket),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title_ticket,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'assigned_by' => Auth::user()->name ?? 'System',
            'message' => "Ticket #{$this->ticket->ticket_number} has been assigned to you",
            'type' => 'ticket_assigned',
            'url' => route('tickets.show', $this->ticket),
        ]);
    }
}