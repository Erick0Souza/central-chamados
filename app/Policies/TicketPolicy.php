<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id || ($user->role === 'tecnico' && $ticket->assigned_to === $user->id);
    }

    public function comment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket) && $ticket->status !== 'encerrado';
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() && $ticket->status !== 'encerrado';
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->status === 'resolvido'
            && ($user->isAdmin()
                || $ticket->user_id === $user->id
                || ($user->role === 'tecnico' && $ticket->assigned_to === $user->id));
    }
}
