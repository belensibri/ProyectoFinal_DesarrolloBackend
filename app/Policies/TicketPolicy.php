<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUsuario() || $user->isTecnico() || $user->isAdministrador();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdministrador()) {
            return true;
        }

        if ($user->isUsuario()) {
            return $user->id === $ticket->usuario_id
                && ! $ticket->isActive();
        }

        return $ticket->tecnico_id === $user->id
            || ($ticket->isActive() && $ticket->tecnico_id === null);
    }

    public function create(User $user): bool
    {
        return $user->isUsuario();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->isClosed()) {
            return false;
        }

        if ($user->isAdministrador()) {
            return true;
        }

        return $user->isUsuario()
            && $user->id === $ticket->usuario_id
            && $ticket->tecnico_id === null
            && $ticket->isActive();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isTecnico()
            && $ticket->isActive()
            && $ticket->tecnico_id === null;
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->isTecnico()
            && $ticket->isInProgress()
            && $ticket->tecnico_id === $user->id
            && $ticket->comments()->where('rol', Comment::ROL_TECNICO)->exists();
    }

    public function reassign(User $user, Ticket $ticket): bool
    {
        return $user->isAdministrador() && $ticket->isInProgress();
    }

    public function addComment(User $user, Ticket $ticket): bool
    {
        if ($ticket->isClosed() || $user->isAdministrador() || ! $ticket->isInProgress()) {
            return false;
        }

        if ($user->isUsuario()) {
            return $ticket->usuario_id === $user->id;
        }

        return $user->isTecnico() && $ticket->tecnico_id === $user->id;
    }

    public function addAttachment(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
