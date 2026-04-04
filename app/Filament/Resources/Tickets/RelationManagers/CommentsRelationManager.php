<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comentarios';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Tickets\Pages\ViewTicket::class;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('usuario_id')
                    ->default(fn () => auth()->id())
                    ->required(),
                Textarea::make('contenido')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contenido')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sin usuario'),
                TextColumn::make('contenido')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (RelationManager $livewire) => auth()->user()->can('addComment', $livewire->getOwnerRecord()))
                    ->after(function (RelationManager $livewire) {
                        \App\Models\TicketHistory::create([
                            'ticket_id' => $livewire->getOwnerRecord()->id,
                            'usuario_id' => auth()->id(),
                            'cambio_descripcion' => 'Se agregó un comentario al ticket.',
                        ]);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Model $record) => auth()->id() === $record->usuario_id || auth()->user()->tipo_usuario === 'ADMINISTRADOR')
                    ->after(function (RelationManager $livewire) {
                        \App\Models\TicketHistory::create([
                            'ticket_id' => $livewire->getOwnerRecord()->id,
                            'usuario_id' => auth()->id(),
                            'cambio_descripcion' => 'Un comentario fue modificado.',
                        ]);
                    }),
                DeleteAction::make()
                    ->visible(fn (Model $record) => auth()->id() === $record->usuario_id || auth()->user()->tipo_usuario === 'ADMINISTRADOR')
                    ->before(function (RelationManager $livewire) {
                        \App\Models\TicketHistory::create([
                            'ticket_id' => $livewire->getOwnerRecord()->id,
                            'usuario_id' => auth()->id(),
                            'cambio_descripcion' => 'Un comentario fue eliminado.',
                        ]);
                    }),
            ])
            ->toolbarActions([]);
    }
}
