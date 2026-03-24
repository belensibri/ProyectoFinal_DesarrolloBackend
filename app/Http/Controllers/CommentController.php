<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        Gate::authorize('addComment', $ticket);

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $request->user()->id,
            'contenido' => $request->contenido
        ]);

        return response()->json($comment, 201);
    }
}
