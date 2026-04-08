<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->placeholder('Sin asignar')
                    ->searchable(),
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->badge(),
                TextColumn::make('prioridad')
                    ->badge(),
                TextColumn::make('fecha_creacion')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fecha_cierre')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Abierto'),
            ])
            ->filters([])
            ->recordActions([
                Action::make('seguir')
                    ->label('Ver ticket')
                    ->icon('heroicon-o-eye')
                    ->visible(function (Ticket $record): bool {
                        if ($record->isClosed()) {
                            return false;
                        }

                        $user = auth()->user();

                        if ($user->isAdministrador()) {
                            return true;
                        }

                        if ($user->isTecnico()) {
                            return $record->tecnico_id === $user->id;
                        }

                        return $user->can('view', $record);
                    })
                    ->url(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record])),

                Action::make('agarrar')
                    ->label('Tomar ticket')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn (Ticket $record) => auth()->user()->can('assign', $record))
                    ->action(fn (Ticket $record) => app(TicketService::class)->assignTicket($record, auth()->user()))
                    ->requiresConfirmation(),

                Action::make('cerrar')
                    ->label('Cerrar ticket')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Ticket $record) => auth()->user()->can('close', $record))
                    ->form([
                        \Filament\Forms\Components\TextInput::make('titulo')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Ticket $record) => $record->titulo),
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
                        \Filament\Forms\Components\Select::make('categoria')
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
                    ])
                    ->action(fn (Ticket $record, array $data) => app(TicketService::class)->closeTicket($record, auth()->user(), $data))
                    ->successNotificationTitle('Ticket cerrado y FAQ creado correctamente.')
                    ->requiresConfirmation(),

                Action::make('reasignar')
                    ->label('Reasignar')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->visible(fn (Ticket $record) => auth()->user()->can('reassign', $record))
                    ->form([
                        \Filament\Forms\Components\Select::make('tecnico_id')
                            ->label('Nuevo técnico')
                            ->options(fn () => User::query()
                                ->where('tipo_usuario', 'TECNICO')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->rule(function (Ticket $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record): void {
                                    if ((int) $value === (int) $record->tecnico_id) {
                                        $fail('Debes seleccionar un técnico distinto al actualmente asignado.');
                                    }
                                };
                            })
                            ->required(),
                    ])
                    ->action(function (Ticket $record, array $data): void {
                        $newTechnician = User::query()
                            ->where('tipo_usuario', 'TECNICO')
                            ->findOrFail($data['tecnico_id']);

                        app(TicketService::class)->reassignTicket($record, $newTechnician, auth()->user());
                    })
                    ->successNotificationTitle('Ticket reasignado correctamente.')
                    ->requiresConfirmation(),

                EditAction::make()
                    ->visible(fn (Ticket $record) => auth()->user()->can('update', $record)),
            ]);
    }
}
