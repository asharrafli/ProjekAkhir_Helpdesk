<?php

namespace App\Notifications;

use App\Models\Tickets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tickets $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('A new ticket has been created.')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . ($this->ticket->title ?? $this->ticket->title_ticket))
                    ->line('Priority: ' . ucfirst($this->ticket->priority))
                    ->action('View Ticket', route('tickets.show', $this->ticket))
                    ->line('Thank you for using our ticketing system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title ?? $this->ticket->title_ticket,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'created_by' => $this->ticket->user->name,
            'message' => "New ticket #{$this->ticket->ticket_number} has been created",
            'type' => 'ticket_created',
            'url' => route('tickets.show', $this->ticket),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title ?? $this->ticket->title_ticket,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'created_by' => $this->ticket->user->name,
            'message' => "New ticket #{$this->ticket->ticket_number} has been created",
            'type' => 'ticket_created',
            'url' => route('tickets.show', $this->ticket),
        ]);
    }
}