<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
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
            \App\Models\TicketHistory::create([
                'ticket_id' => $this->record->id,
                'usuario_id' => auth()->id(),
                'cambio_descripcion' => $description,
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
                ->visible(fn () => in_array(auth()->user()->tipo_usuario, ['TECNICO', 'ADMINISTRADOR']) && is_null($this->record->tecnico_id))
                ->action(function () {
                    $this->record->update([
                        'tecnico_id' => auth()->id(),
                        'estado' => 'en_progreso',
                    ]);

                    \App\Models\TicketHistory::create([
                        'ticket_id' => $this->record->id,
                        'usuario_id' => auth()->id(),
                        'cambio_descripcion' => 'Ticket asignado a ' . auth()->user()->name . ' y cambiado a En Progreso.',
                    ]);

                    $this->refreshFormData(['tecnico_id', 'estado']);
                })
                ->requiresConfirmation(),

            Action::make('resolver')
                ->label('Resolver Ticket')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => 
                    in_array($this->record->estado, ['abierto', 'en_progreso']) && 
                    (auth()->user()->tipo_usuario === 'ADMINISTRADOR' || (auth()->user()->tipo_usuario === 'TECNICO' && $this->record->tecnico_id === auth()->id()))
                )
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
                        ])
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('solucion')
                        ->label('Solución / Contenido')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado' => 'resuelto',
                    ]);

                    \App\Models\TicketHistory::create([
                        'ticket_id' => $this->record->id,
                        'usuario_id' => auth()->id(),
                        'cambio_descripcion' => 'Ticket marcado como resuelto por el técnico. A la espera de validación del usuario.',
                    ]);

                    \App\Models\FaqArticle::create([
                        'titulo' => $this->record->titulo,
                        'contenido' => $data['solucion'],
                        'categoria' => $data['categoria'],
                        'usuario_id' => auth()->id(),
                    ]);
                    $this->refreshFormData(['estado']);
                })
                ->successNotificationTitle('Ticket marcado como resuelto.')
                ->requiresConfirmation(),

            Action::make('aceptar_resolucion')
                ->label('Aceptar Solución')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => 
                    $this->record->estado === 'resuelto' && 
                    auth()->user()->tipo_usuario === 'USUARIO' && $this->record->usuario_id === auth()->id()
                )
                ->action(function () {
                    $this->record->update([
                        'estado' => 'cerrado',
                        'fecha_cierre' => now(),
                    ]);

                    \App\Models\TicketHistory::create([
                        'ticket_id' => $this->record->id,
                        'usuario_id' => auth()->id(),
                        'cambio_descripcion' => 'El usuario aceptó la solución. El ticket ha sido cerrado.',
                    ]);
                    $this->refreshFormData(['estado', 'fecha_cierre']);
                })
                ->requiresConfirmation()
                ->modalHeading('Aceptar Solución')
                ->modalDescription('¿Estás seguro que la solución resolvió tu problema?'),

            Action::make('rechazar_resolucion')
                ->label('Rechazar Solución')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => 
                    $this->record->estado === 'resuelto' && 
                    auth()->user()->tipo_usuario === 'USUARIO' && $this->record->usuario_id === auth()->id()
                )
                ->action(function () {
                    $this->record->update([
                        'estado' => 'en_progreso',
                    ]);

                    \App\Models\TicketHistory::create([
                        'ticket_id' => $this->record->id,
                        'usuario_id' => auth()->id(),
                        'cambio_descripcion' => 'El usuario rechazó la solución. El ticket vuelve a estar En Progreso.',
                    ]);
                    $this->refreshFormData(['estado']);
                })
                ->requiresConfirmation()
                ->modalHeading('Rechazar Solución')
                ->modalDescription('¿El problema no fue resuelto?'),

            DeleteAction::make(),
        ];
    }
}
