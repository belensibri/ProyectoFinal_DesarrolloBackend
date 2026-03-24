<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Hidden::make('usuario_id')
                    ->default(fn () => auth()->id()),

                \Filament\Forms\Components\TextInput::make('titulo')
                    ->required(),

                \Filament\Forms\Components\Textarea::make('descripcion')
                    ->required()
                    ->columnSpanFull(),

                \Filament\Forms\Components\Select::make('estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'en_progreso' => 'En Progreso',
                        'resuelto' => 'Resuelto',
                        'cerrado' => 'Cerrado',
                    ])
                    ->required()
                    ->default('abierto')
                    ->visible(fn () => in_array(auth()->user()->tipo_usuario, ['ADMINISTRADOR', 'TECNICO'])),

                \Filament\Forms\Components\Select::make('prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                    ])
                    ->required()
                    ->default('media'),

                \Filament\Forms\Components\DateTimePicker::make('fecha_creacion')
                    ->default(now())
                    ->disabled()
                    ->required(),

                \Filament\Forms\Components\DateTimePicker::make('fecha_cierre')
                    ->disabled()
                    ->visible(fn () => in_array(auth()->user()->tipo_usuario, ['ADMINISTRADOR', 'TECNICO'])),
            ]);
    }
}
