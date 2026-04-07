<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function assignTicket(Ticket $ticket, User $technician): Ticket
    {
        if (! $ticket->isActive()) {
            throw ValidationException::withMessages([
                'ticket' => 'Solo los tickets activos pueden ser tomados por un técnico.',
            ]);
        }

        if ($ticket->tecnico_id !== null) {
            throw ValidationException::withMessages([
                'ticket' => 'El ticket ya fue asignado a un técnico.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $technician) {
            $ticket->forceFill([
                'tecnico_id' => $technician->id,
                'estado' => Ticket::ESTADO_EN_PROCESO,
            ])->save();

            $this->logHistory(
                $ticket,
                $technician,
                'ticket_asignado',
                'El ticket fue tomado por el técnico y pasó a en_proceso.'
            );

            return $ticket->refresh();
        });
    }

    public function addComment(Ticket $ticket, User $actor, array $data): Comment
    {
        if ($ticket->isClosed()) {
            throw ValidationException::withMessages([
                'ticket' => 'No se pueden agregar comentarios a tickets cerrados.',
            ]);
        }

        $expectedRole = $actor->isTecnico() ? Comment::ROL_TECNICO : Comment::ROL_USUARIO;

        if (($data['rol'] ?? null) !== $expectedRole) {
            throw ValidationException::withMessages([
                'rol' => 'El rol del comentario no coincide con el rol del usuario autenticado.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $actor, $data) {
            $comment = $ticket->comments()->create([
                'usuario_id' => $actor->id,
                'rol' => $data['rol'],
                'contenido' => $data['contenido'],
            ]);

            foreach ($this->normalizeAttachments($data['attachments'] ?? []) as $attachment) {
                $this->storeAttachment($ticket, $actor, $attachment);
            }

            $this->logHistory(
                $ticket,
                $actor,
                'comentario_agregado',
                sprintf('Se agregó un comentario de tipo %s.', $data['rol'])
            );

            return $comment->refresh();
        });
    }

    public function closeTicket(Ticket $ticket, User $technician, array $faqData): Ticket
    {
        if (! $ticket->isInProgress()) {
            throw ValidationException::withMessages([
                'ticket' => 'Solo los tickets en_proceso pueden cerrarse.',
            ]);
        }

        if ($ticket->tecnico_id !== $technician->id) {
            throw ValidationException::withMessages([
                'ticket' => 'Solo el técnico asignado puede cerrar este ticket.',
            ]);
        }

        if (! $ticket->comments()->where('rol', Comment::ROL_TECNICO)->exists()) {
            throw ValidationException::withMessages([
                'ticket' => 'Debe existir al menos un comentario técnico antes de cerrar el ticket.',
            ]);
        }

        if ($ticket->faqArticle()->exists()) {
            throw ValidationException::withMessages([
                'ticket' => 'El ticket ya tiene un registro FAQ asociado.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $technician, $faqData) {
            $ticket->forceFill([
                'estado' => Ticket::ESTADO_CERRADO,
                'fecha_cierre' => now(),
            ])->save();

            $ticket->faqArticle()->create([
                'titulo' => $faqData['titulo'],
                'descripcion_problema' => $faqData['descripcion_problema'] ?? null,
                'resolucion' => $faqData['resolucion'],
                'causa_raiz' => $faqData['causa_raiz'],
                'tipo_resolucion' => $faqData['tipo_resolucion'],
                'es_reutilizable' => $faqData['es_reutilizable'],
                'categoria' => $faqData['categoria'] ?? 'otro',
                'usuario_id' => $technician->id,
            ]);

            $this->logHistory(
                $ticket,
                $technician,
                'ticket_cerrado',
                'El ticket fue cerrado y la resolución quedó documentada en la base de conocimiento.'
            );

            return $ticket->refresh()->load('faqArticle');
        });
    }

    public function reassignTicket(Ticket $ticket, User $newTechnician, User $admin): Ticket
    {
        if (! $admin->isAdministrador()) {
            throw ValidationException::withMessages([
                'ticket' => 'Solo un administrador puede reasignar tickets.',
            ]);
        }

        if ($ticket->isClosed() || ! $ticket->isInProgress()) {
            throw ValidationException::withMessages([
                'ticket' => 'Solo se pueden reasignar tickets en_proceso.',
            ]);
        }

        if (! $newTechnician->isTecnico()) {
            throw ValidationException::withMessages([
                'tecnico_id' => 'El usuario seleccionado no es un técnico válido.',
            ]);
        }

        if ($ticket->tecnico_id === $newTechnician->id) {
            throw ValidationException::withMessages([
                'tecnico_id' => 'Debes seleccionar un técnico distinto al actualmente asignado.',
            ]);
        }

        $oldTechnician = $ticket->technician;

        return DB::transaction(function () use ($ticket, $newTechnician, $admin, $oldTechnician) {
            $ticket->forceFill([
                'tecnico_id' => $newTechnician->id,
            ])->save();

            $previousTechnicianName = $oldTechnician?->name ?? 'Sin técnico asignado';
            $newTechnicianName = $newTechnician->name;

            $this->logHistory(
                $ticket,
                $admin,
                'ticket_reasignado',
                sprintf(
                    'Ticket reasignado de %s a %s.',
                    $previousTechnicianName,
                    $newTechnicianName
                )
            );

            return $ticket->refresh()->load('technician');
        });
    }

    private function logHistory(Ticket $ticket, User $user, string $action, ?string $comment = null): TicketHistory
    {
        return $ticket->ticketHistories()->create([
            'usuario_id' => $user->id,
            'accion' => $action,
            'comentario' => $comment,
        ]);
    }

    private function storeAttachment(Ticket $ticket, User $user, UploadedFile|string $attachment): Attachment
    {
        return $ticket->attachments()->create($this->buildAttachmentPayload($ticket, $user, $attachment));
    }

    /**
     * @param  array<int, UploadedFile|string>|UploadedFile|string|null  $attachments
     * @return array<int, UploadedFile|string>
     */
    private function normalizeAttachments(UploadedFile|string|array|null $attachments): array
    {
        if ($attachments === null || $attachments === '') {
            return [];
        }

        return array_values(array_filter(
            is_array($attachments) ? $attachments : [$attachments],
            fn (UploadedFile|string|null $attachment): bool => filled($attachment),
        ));
    }

    /**
     * @return array<string, string|int>
     */
    private function buildAttachmentPayload(Ticket $ticket, User $user, UploadedFile|string $attachment): array
    {
        if ($attachment instanceof UploadedFile) {
            $path = $attachment->store('ticket-attachments', 'public');
            $size = $attachment->getSize() ?? 0;
        } else {
            $path = $attachment;
            $size = Storage::disk('public')->exists($path)
                ? Storage::disk('public')->size($path)
                : 0;
        }

        return [
            'ticket_id' => $ticket->id,
            'usuario_id' => $user->id,
            'ruta_archivo' => $path,
            'size' => (string) $size,
        ];
    }
}
