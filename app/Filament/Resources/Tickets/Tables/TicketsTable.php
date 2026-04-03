<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usuario_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tecnico_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->searchable(),
                TextColumn::make('prioridad')
                    ->searchable(),
                TextColumn::make('fecha_creacion')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fecha_cierre')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('seguir')
                    ->label('Seguir ticket')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => TicketResource::getUrl('view', ['record' => $record])),

                Action::make('agarrar')
                    ->label('Agarrar Ticket')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn ($record) => $record !== null && in_array(auth()->user()->tipo_usuario, ['TECNICO', 'ADMINISTRADOR']) && is_null($record->tecnico_id))
                    ->action(function ($record) {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                            $record->update([
                                'tecnico_id' => auth()->id(),
                                'estado' => 'en_progreso',
                            ]);

                            \App\Models\TicketHistory::create([
                                'ticket_id' => $record->id,
                                'usuario_id' => auth()->id(),
                                'cambio_descripcion' => 'Ticket asignado a ' . auth()->user()->name . ' y cambiado a En Progreso.',
                            ]);
                        });
                    })
                    ->requiresConfirmation(),

                Action::make('cerrar')
                    ->label('Cerrar Ticket')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record !== null &&
                        $record->estado !== 'cerrado' && 
                        (auth()->user()->tipo_usuario === 'ADMINISTRADOR' || (auth()->user()->tipo_usuario === 'TECNICO' && $record->tecnico_id === auth()->id()))
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
                    ->action(function ($record, array $data) {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                            $record->update([
                                'estado' => 'cerrado',
                                'fecha_cierre' => now(),
                            ]);

                            \App\Models\TicketHistory::create([
                                'ticket_id' => $record->id,
                                'usuario_id' => auth()->id(),
                                'cambio_descripcion' => 'Ticket cerrado. Se documento la solucion en FAQ.',
                            ]);

                            \App\Models\FaqArticle::create([
                                'titulo' => $record->titulo,
                                'contenido' => $data['solucion'],
                                'categoria' => $data['categoria'],
                                'usuario_id' => auth()->id(),
                            ]);
                        });
                    })
                    ->requiresConfirmation(),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
