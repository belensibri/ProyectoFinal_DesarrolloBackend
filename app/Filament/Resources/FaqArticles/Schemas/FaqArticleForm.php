<?php

namespace App\Filament\Resources\FaqArticles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FaqArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('contenido')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('categoria')
                    ->required(),
                TextInput::make('usuario_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
