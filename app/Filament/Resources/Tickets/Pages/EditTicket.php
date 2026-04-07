<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use App\Models\Comment;
use App\Services\TicketService;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected array $oldRecordData = [];

    protected function beforeSave(): void
    {
        $this->oldRecordData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        $changes = collect($this->record->getChanges())
            ->except(['updated_at'])
            ->map(function ($newValue, string $field): string {
                $labels = [
                    'titulo' => 'titulo',
                    'descripcion' => 'descripcion',
                    'estado' => 'estado',
                    'prioridad' => 'prioridad',
                    'tecnico_id' => 'tecnico asignado',
                    'fecha_cierre' => 'fecha de cierre',
                ];

                $oldValue = $this->oldRecordData[$field] ?? 'vacio';
                $newValue = $newValue ?? 'vacio';

                return sprintf(
                    'Se actualizo %s de "%s" a "%s".',
                    $labels[$field] ?? $field,
                    (string) $oldValue,
                    (string) $newValue,
                );
            })
            ->values();

        foreach ($changes as $description) {
            $this->record->ticketHistories()->create([
                'usuario_id' => auth()->id(),
                'accion' => 'ticket_actualizado',
                'comentario' => $description,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('seguir')
                ->label('Seguir ticket')
                ->icon('heroicon-o-eye')
                ->url(fn () => TicketResource::getUrl('view', ['record' => $this->record])),

            Action::make('agarrar')
                ->label('Agarrar Ticket')
                ->icon('heroicon-o-hand-raised')
                ->visible(fn () => auth()->user()->can('assign', $this->record))
                ->action(function () {
                    app(TicketService::class)->assignTicket($this->record, auth()->user());

                    $this->refreshFormData(['tecnico_id', 'estado']);
                })
                ->requiresConfirmation(),

            Action::make('resolver')
                ->label('Resolver Ticket')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => auth()->user()->can('close', $this->record))
                ->form([
                    \Filament\Forms\Components\Select::make('categoria')
                        ->label('Categoría de FAQ')
                        ->options([
                            'backend' => 'Backend',
                            'frontend' => 'Frontend',
                            'bases_de_datos' => 'Bases de Datos',
                            'devops' => 'DevOps',
                            'testing' => 'Testing',
                            'seguridad' => 'Seguridad',
                            'otro' => 'Otro',
                        ])
                        ->default('otro'),
                    \Filament\Forms\Components\TextInput::make('titulo')
                        ->required()
                        ->default(fn () => $this->record->titulo),
                    \Filament\Forms\Components\Textarea::make('descripcion_problema')
                        ->label('Descripción del problema'),
                    \Filament\Forms\Components\Textarea::make('resolucion')
                        ->label('Resolución')
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('causa_raiz')
                        ->label('Causa raíz')
                        ->required(),
                    \Filament\Forms\Components\Select::make('tipo_resolucion')
                        ->options([
                            'workaround' => 'Workaround',
                            'solucion_definitiva' => 'Solución definitiva',
                        ])
                        ->required(),
                    \Filament\Forms\Components\Toggle::make('es_reutilizable')
                        ->default(true)
                        ->required(),
                ])
                ->action(function (array $data) {
                    app(TicketService::class)->closeTicket($this->record, auth()->user(), $data);
                    $this->refreshFormData(['estado', 'fecha_cierre']);
                })
                ->successNotificationTitle('Ticket cerrado y FAQ creado correctamente.')
                ->requiresConfirmation(),

            Action::make('agregarBitacora')
                ->label('Agregar bitácora')
                ->visible(fn () => auth()->user()->can('create', [Comment::class, $this->record, Comment::ROL_TECNICO]))
                ->form([
                    \Filament\Forms\Components\Textarea::make('contenido')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    app(TicketService::class)->addComment($this->record, auth()->user(), [
                        'rol' => Comment::ROL_TECNICO,
                        'contenido' => $data['contenido'],
                    ]);
                }),
        ];
    }
}
