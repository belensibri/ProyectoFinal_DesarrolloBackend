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
        TicketHistory::create([
            'ticket_id' => $this->record->id,
            'usuario_id' => auth()->id(),
            'cambio_descripcion' => 'Ticket creado.',
        ]);
    }
}
