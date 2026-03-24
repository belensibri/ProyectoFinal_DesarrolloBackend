<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->tipo_usuario === 'TECNICO') {
            return Ticket::where('tecnico_id', $user->id)
                         ->orWhere('estado', 'abierto')
                         ->with(['department', 'user'])
                         ->get();
        }
        return Ticket::where('usuario_id', $user->id)
                     ->with(['department', 'technician'])
                     ->get();
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create(array_merge($request->validated(), [
            'usuario_id' => $request->user()->id,
            'estado' => 'abierto'
        ]));

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $request->user()->id,
            'cambio_descripcion' => 'Ticket creado con estado abierto'
        ]);

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        return $ticket->load(['comments.user', 'attachments', 'ticket_histories', 'department', 'technician', 'user']);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);
        $ticket->update($request->validated());
        return response()->json($ticket);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);
        
        $ticket->update([
            'tecnico_id' => $request->tecnico_id,
            'estado' => 'en_progreso'
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $request->user()->id,
            'cambio_descripcion' => "Ticket asignado al técnico {$request->tecnico_id} y estado cambiado a en_progreso"
        ]);

        return response()->json($ticket);
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        Gate::authorize('updateStatus', [$ticket, $request->estado]);
        $nuevoEstado = $request->estado;
        
        $ticket->update(['estado' => $nuevoEstado]);
        
        if ($nuevoEstado === 'cerrado') {
            $ticket->update(['fecha_cierre' => now()]);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $request->user()->id,
            'cambio_descripcion' => "Estado cambiado a {$nuevoEstado}"
        ]);

        return response()->json($ticket);
    }
}
