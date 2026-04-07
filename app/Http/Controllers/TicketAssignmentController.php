<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class TicketAssignmentController extends Controller
{
    public function store(AssignTicketRequest $request, Ticket $ticket, TicketService $ticketService): JsonResponse
    {
        $assignedTicket = $ticketService->assignTicket($ticket, $request->user());

        return response()->json($assignedTicket);
    }
}
