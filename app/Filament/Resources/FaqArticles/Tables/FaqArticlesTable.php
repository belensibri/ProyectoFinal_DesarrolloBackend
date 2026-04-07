<?php

namespace App\Filament\Resources\FaqArticles\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('categoria')
                    ->searchable(),
                TextColumn::make('ticket.titulo')
                    ->label('Ticket'),
                TextColumn::make('user.name')
                    ->label('Documentado por')
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
            ->recordActions([])
            ->toolbarActions([]);
    }
}
