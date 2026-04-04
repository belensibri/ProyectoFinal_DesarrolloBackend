<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
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
        return false;
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
                TextColumn::make('ruta_archivo')
                    ->label('Archivo')
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
                    ->visible(fn (RelationManager $livewire) => auth()->user()->can('addAttachment', $livewire->getOwnerRecord()))
                    ->after(function (RelationManager $livewire) {
                        \App\Models\TicketHistory::create([
                            'ticket_id' => $livewire->getOwnerRecord()->id,
                            'usuario_id' => auth()->id(),
                            'cambio_descripcion' => 'Se agregó un adjunto al ticket.',
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
                            'cambio_descripcion' => 'Un adjunto fue modificado.',
                        ]);
                    }),
                DeleteAction::make()
                    ->visible(fn (Model $record) => auth()->id() === $record->usuario_id || auth()->user()->tipo_usuario === 'ADMINISTRADOR')
                    ->before(function (RelationManager $livewire) {
                        \App\Models\TicketHistory::create([
                            'ticket_id' => $livewire->getOwnerRecord()->id,
                            'usuario_id' => auth()->id(),
                            'cambio_descripcion' => 'Un adjunto fue eliminado.',
                        ]);
                    }),
            ])
            ->toolbarActions([]);
    }
}
