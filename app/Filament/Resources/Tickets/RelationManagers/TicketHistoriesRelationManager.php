<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticket_histories';

    protected static ?string $title = 'Historial';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Tickets\Pages\ViewTicket::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cambio_descripcion')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sistema'),
                TextColumn::make('cambio_descripcion')
                    ->label('Cambio')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
