<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen del ticket')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('titulo')
                            ->label('Titulo'),
                        TextEntry::make('estado')
                            ->badge(),
                        TextEntry::make('prioridad')
                            ->badge(),
                        TextEntry::make('user.name')
                            ->label('Creado por')
                            ->placeholder('Sin usuario'),
                        TextEntry::make('technician.name')
                            ->label('Tecnico asignado')
                            ->placeholder('Sin asignar'),
                        TextEntry::make('fecha_creacion')
                            ->label('Fecha de creacion')
                            ->dateTime(),
                        TextEntry::make('fecha_cierre')
                            ->label('Fecha de cierre')
                            ->dateTime()
                            ->placeholder('Aun abierto'),
                        TextEntry::make('created_at')
                            ->label('Registrado')
                            ->dateTime(),
                        TextEntry::make('descripcion')
                            ->label('Descripcion')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('agregarComentario')
                ->label('Add comentario')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->visible(fn () => auth()->user()->can('addComment', $this->record))
                ->form([
                    Textarea::make('contenido')
                        ->label('Comentario')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $this->record->comments()->create([
                        'contenido' => $data['contenido'],
                        'usuario_id' => auth()->id(),
                    ]);

                    $this->record->ticket_histories()->create([
                        'usuario_id' => auth()->id(),
                        'cambio_descripcion' => 'Se agrego un comentario al ticket.',
                    ]);
                })
                ->successNotificationTitle('Comentario agregado correctamente.'),
            EditAction::make(),
        ];
    }
}
