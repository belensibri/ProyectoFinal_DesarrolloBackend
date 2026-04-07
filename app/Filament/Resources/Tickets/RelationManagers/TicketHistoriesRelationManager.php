<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketHistories';

    protected static ?string $title = 'Historial';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Tickets\Pages\ViewTicket::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comentario')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sistema'),
                TextColumn::make('accion')
                    ->label('Acción')
                    ->badge(),
                TextColumn::make('comentario')
                    ->label('Detalle')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
