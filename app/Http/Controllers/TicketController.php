<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = Ticket::query()
            ->with(['user', 'technician', 'faqArticle'])
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return response()->json($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create([
            'usuario_id' => $request->user()->id,
            'titulo' => $request->validated('titulo'),
            'descripcion' => $request->validated('descripcion'),
            'categoria' => $request->validated('categoria', 'otro'),
            'prioridad' => $request->validated('prioridad'),
            'estado' => Ticket::ESTADO_ACTIVO,
            'fecha_creacion' => now(),
        ]);

        $ticket->ticketHistories()->create([
            'usuario_id' => $request->user()->id,
            'accion' => 'ticket_creado',
            'comentario' => 'El usuario registró un nuevo ticket.',
        ]);

        return response()->json($ticket->fresh(), 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return response()->json($ticket->load(['user', 'technician', 'comments', 'faqArticle']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket->fill($request->validated());
        $ticket->save();

        $ticket->ticketHistories()->create([
            'usuario_id' => $request->user()->id,
            'accion' => 'ticket_actualizado',
            'comentario' => 'Se actualizaron los datos editables del ticket.',
        ]);

        return response()->json($ticket->fresh());
    }
}
