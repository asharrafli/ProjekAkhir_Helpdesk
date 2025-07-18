<?php

namespace App\Observers;

use App\Models\TelegramNotifiable;
use App\Models\Tickets;
use App\Notifications\AssignTicketNotification;
use App\Notifications\NewCreateTicketNotification;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketAssignedNotification;

class TicketObserver
{
    public function created(Tickets $ticket)
    {
        // Kirim notifikasi saat tiket dibuat
        $notifiable = new TelegramNotifiable();
        $notifiable->notify(new NewCreateTicketNotification($ticket->load('user')));
    }

    public function updated(Tickets $ticket)
    {
        // Kirim notifikasi saat tiket di-assign
        if ($ticket->isDirty('assigned_to') && $ticket->assigned_to) {
            $technician = $ticket->assignedTo; // Relasi ke User
            $notifiable = new TelegramNotifiable();
            $notifiable->notify(new TicketAssignedNotification(
                $ticket->load('user'), 
                $technician
            ));
        }
    }
}