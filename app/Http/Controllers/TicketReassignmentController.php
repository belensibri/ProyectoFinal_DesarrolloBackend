<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReassignTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class TicketReassignmentController extends Controller
{
    public function store(ReassignTicketRequest $request, Ticket $ticket, TicketService $ticketService): JsonResponse
    {
        $newTechnician = User::query()->findOrFail($request->integer('tecnico_id'));

        $reassignedTicket = $ticketService->reassignTicket($ticket, $newTechnician, $request->user());

        return response()->json($reassignedTicket);
    }
}
