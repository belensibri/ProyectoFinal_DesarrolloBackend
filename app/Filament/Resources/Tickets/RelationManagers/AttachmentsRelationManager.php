<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Adjuntos';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Tickets\Pages\ViewTicket::class;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('usuario_id')
                    ->default(fn () => auth()->id())
                    ->required(),
                TextInput::make('ruta_archivo')
                    ->label('Ruta del archivo')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ruta_archivo')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sin usuario'),
                ImageColumn::make('ruta_archivo')
                    ->label('Vista previa')
                    ->disk('public')
                    ->square(),
                TextColumn::make('ruta_archivo')
                    ->label('Archivo')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('size')
                    ->label('Tamaño (bytes)'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
