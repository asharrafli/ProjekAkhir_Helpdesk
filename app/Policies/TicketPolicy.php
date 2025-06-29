<?php

namespace App\Policies;

use App\Models\Tickets;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Tickets $ticket)
    {
        // Admin and manager can view all tickets
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Technicians can only view tickets assigned to them
        if ($user->hasRole('technician')) {
            return $ticket->assigned_to === $user->id;
        }

        // Users can view their own tickets
        return $ticket->user_id === $user->id;
    }

    public function create(User $user)
    {
        return $user->can('create-tickets');
    }

    public function update(User $user, Tickets $ticket)
    {
        // Admin and manager can update all tickets
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Technicians can only update tickets assigned to them
        if ($user->hasRole('technician') && $user->can('edit-tickets')) {
            return $ticket->assigned_to === $user->id;
        }

        // Users can only update their own open tickets
        if ($ticket->user_id === $user->id && $ticket->status === 'open') {
            return true;
        }

        return false;
    }

    public function delete(User $user, Tickets $ticket)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function assign(User $user, Tickets $ticket)
    {
        return $user->can('assign-tickets');
    }

    public function claim(User $user, Tickets $ticket)
    {
        return $user->hasRole('technician') && 
               $ticket->status === 'open' && 
               is_null($ticket->assigned_to);
    }
}