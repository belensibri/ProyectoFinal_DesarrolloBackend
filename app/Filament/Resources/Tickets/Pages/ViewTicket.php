<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Livewire\Tickets\TicketChat;
use App\Filament\Resources\Tickets\TicketResource;
use App\Services\TicketService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public string $activeContentTab = 'summary';

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
                ->label('Agregar comentario')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->visible(fn () => auth()->user()->can('addComment', $this->record))
                ->form([
                    Textarea::make('contenido')
                        ->label('Mensaje')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('Archivos')
                        ->multiple()
                        ->disk('public')
                        ->acceptedFileTypes(['image/png', 'image/jpeg'])
                        ->maxSize(2048),
                ])
                ->action(function (array $data): void {
                    app(TicketService::class)->addMessageToTicket($this->record, auth()->user(), [
                        'contenido' => $data['contenido'],
                        'attachments' => $data['attachments'] ?? [],
                    ]);
                })
                ->successNotificationTitle('Comentario agregado correctamente.'),
            EditAction::make()
                ->visible(fn () => auth()->user()->can('update', $this->record)),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('TicketTabs')
                    ->livewireProperty('activeContentTab')
                    ->tabs([
                        'summary' => Tab::make('Resumen')
                            ->schema([
                                Section::make('Resumen del ticket')
                                    ->schema([
                                        EmbeddedSchema::make('infolist'),
                                    ]),
                                $this->getRelationManagersContentComponent(),
                            ]),
                        'conversation' => Tab::make('Conversacion')
                            ->schema([
                                Livewire::make(TicketChat::class, [
                                    'record' => $this->record,
                                ])
                                    ->key('ticket-chat-' . $this->record->getKey()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
