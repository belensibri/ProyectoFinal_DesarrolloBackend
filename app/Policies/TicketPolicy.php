<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->usuario_id || $user->id === $ticket->tecnico_id || ($user->tipo_usuario === 'TECNICO' && $ticket->estado === 'abierto');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->usuario_id;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->tipo_usuario === 'TECNICO' && $ticket->estado === 'abierto';
    }

    public function updateStatus(User $user, Ticket $ticket, string $nuevoEstado): bool
    {
        if ($user->tipo_usuario === 'TECNICO' && $user->id === $ticket->tecnico_id) {
            // Technician can change to en_progreso or resuelto
            return in_array($nuevoEstado, ['en_progreso', 'resuelto']);
        }

        if ($user->tipo_usuario === 'USUARIO' && $user->id === $ticket->usuario_id) {
            // User can accept (cerrado) or reject (en_progreso) if it was resuelto
            if ($ticket->estado === 'resuelto') {
                return in_array($nuevoEstado, ['cerrado', 'en_progreso']);
            }
        }

        return false;
    }

    public function addComment(User $user, Ticket $ticket): bool
    {
        return $ticket->estado !== 'cerrado' && ($user->id === $ticket->usuario_id || $user->id === $ticket->tecnico_id);
    }

    public function addAttachment(User $user, Ticket $ticket): bool
    {
        return $ticket->estado !== 'cerrado' && ($user->id === $ticket->usuario_id || $user->id === $ticket->tecnico_id);
    }
}
