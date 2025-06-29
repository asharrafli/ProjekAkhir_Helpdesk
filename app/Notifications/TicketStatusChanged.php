<?php

namespace App\Notifications;

use App\Models\Tickets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tickets $ticket,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The status of your ticket has been updated.')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title_ticket)
                    ->line('Old Status: ' . ucfirst($this->oldStatus))
                    ->line('New Status: ' . ucfirst($this->newStatus))
                    ->action('View Ticket', route('tickets.show', $this->ticket))
                    ->line('Thank you for using our ticketing system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title_ticket,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_by' => auth()->user()->name ?? 'System',
            'message' => "Ticket #{$this->ticket->ticket_number} status changed from {$this->oldStatus} to {$this->newStatus}",
            'type' => 'ticket_status_changed',
            'url' => route('tickets.show', $this->ticket),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title_ticket,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_by' => auth()->user()->name ?? 'System',
            'message' => "Ticket #{$this->ticket->ticket_number} status changed from {$this->oldStatus} to {$this->newStatus}",
            'type' => 'ticket_status_changed',
            'url' => route('tickets.show', $this->ticket),
        ]);
    }
}