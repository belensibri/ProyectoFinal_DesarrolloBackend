<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUsuario() || $user->isTecnico() || $user->isAdministrador();
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->can('view', $comment->ticket);
    }

    public function create(User $user, Ticket $ticket, string $rol): bool
    {
        if ($ticket->isClosed() || $user->isAdministrador() || ! $ticket->isInProgress()) {
            return false;
        }

        if ($user->isUsuario()) {
            return $ticket->usuario_id === $user->id && $rol === Comment::ROL_USUARIO;
        }

        return $user->isTecnico()
            && $ticket->tecnico_id === $user->id
            && $rol === Comment::ROL_TECNICO;
    }

    public function update(User $user, Comment $comment): bool
    {
        return false;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return false;
    }
}
