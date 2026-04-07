<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class TicketClosureController extends Controller
{
    public function store(CloseTicketRequest $request, Ticket $ticket, TicketService $ticketService): JsonResponse
    {
        $closedTicket = $ticketService->closeTicket($ticket, $request->user(), $request->validated());

        return response()->json($closedTicket);
    }
}
