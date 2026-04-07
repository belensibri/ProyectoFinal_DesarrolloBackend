<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('usuario_id')
                    ->default(fn () => auth()->id()),

                Hidden::make('estado')
                    ->default(Ticket::ESTADO_ACTIVO),

                TextInput::make('titulo')
                    ->required()
                    ->maxLength(255),

                Textarea::make('descripcion')
                    ->required()
                    ->columnSpanFull(),

                Select::make('categoria')
                    ->options([
                        'backend' => 'Backend',
                        'frontend' => 'Frontend',
                        'bases_de_datos' => 'Bases de Datos',
                        'devops' => 'DevOps',
                        'testing' => 'Testing',
                        'seguridad' => 'Seguridad',
                        'otro' => 'Otro',
                    ])
                    ->default('otro')
                    ->required(),

                Select::make('prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                    ])
                    ->required()
                    ->default('media'),

                Select::make('estado')
                    ->options([
                        Ticket::ESTADO_ACTIVO => 'Activo',
                        'en_progreso' => 'En Progreso',
                        Ticket::ESTADO_CERRADO => 'Cerrado',
                    ])
                    ->visible(fn (?Ticket $record) => auth()->user()->isAdministrador() && $record !== null)
                    ->disabled(),

                DateTimePicker::make('fecha_creacion')
                    ->default(now())
                    ->disabled()
                    ->dehydrated(false)
                    ->required(),

                DateTimePicker::make('fecha_cierre')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (?Ticket $record) => $record !== null),

                Placeholder::make('ticket_lifecycle_notice')
                    ->label('Flujo')
                    ->content('Los tickets nuevos se crean como activo, un técnico los toma para pasar a en_proceso y solo el técnico asignado puede cerrarlos con FAQ.')
                    ->columnSpanFull(),
            ]);
    }
}
