<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, Ticket $ticket)
    {
        Gate::authorize('addAttachment', $ticket);

        $path = $request->file('archivo')->store('attachments', 'public');

        $attachment = Attachment::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $request->user()->id,
            'ruta_archivo' => $path
        ]);

        return response()->json($attachment, 201);
    }
}
