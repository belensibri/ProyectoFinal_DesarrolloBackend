<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class TicketCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket, TicketService $ticketService): JsonResponse
    {
        $comment = $ticketService->addComment($ticket, $request->user(), $request->validated());

        return response()->json($comment, 201);
    }
}
