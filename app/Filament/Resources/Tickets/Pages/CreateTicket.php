<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\TicketHistory;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return TicketResource::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->record->ticketHistories()->create([
            'usuario_id' => auth()->id(),
            'accion' => 'ticket_creado',
            'comentario' => 'Ticket creado.',
        ]);
    }
}
