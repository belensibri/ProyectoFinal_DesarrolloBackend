<?php

namespace App\Filament\Resources\FaqArticles\Tables;

use App\Filament\Resources\FaqArticles\FaqArticleResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => FaqArticleResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('titulo')
                    ->searchable()
                    ->weight('semibold'),
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
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => FaqArticleResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
