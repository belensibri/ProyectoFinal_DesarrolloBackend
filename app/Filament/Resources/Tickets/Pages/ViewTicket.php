<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Comment;
use App\Services\TicketService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
        $user = auth()->user();

        return [
            Action::make('agregarComentario')
                ->label($user->isTecnico() ? 'Agregar bitácora' : 'Agregar comentario')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->visible(fn () => $user->can('addComment', $this->record))
                ->form([
                    Textarea::make('contenido')
                        ->label('Comentario')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('Imágenes adjuntas')
                        ->disk('public')
                        ->directory('ticket-attachments')
                        ->image()
                        ->imageEditor()
                        ->multiple()
                        ->acceptedFileTypes(['image/png', 'image/jpeg'])
                        ->visible(fn () => auth()->user()->can('addComment', $this->record)),
                ])
                ->action(function (array $data): void {
                    app(TicketService::class)->addComment($this->record, auth()->user(), [
                        'rol' => auth()->user()->isTecnico() ? Comment::ROL_TECNICO : Comment::ROL_USUARIO,
                        'contenido' => $data['contenido'],
                        'attachments' => $data['attachments'] ?? [],
                    ]);
                })
                ->successNotificationTitle('Comentario agregado correctamente.'),
            EditAction::make()
                ->visible(fn () => auth()->user()->can('update', $this->record)),
        ];
    }
}
