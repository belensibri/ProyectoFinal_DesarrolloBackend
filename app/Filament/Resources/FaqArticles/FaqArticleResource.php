<?php

namespace App\Filament\Resources\FaqArticles;

use App\Filament\Resources\FaqArticles\Pages\ViewFaqArticle;
use App\Filament\Resources\FaqArticles\Pages\ListFaqArticles;
use App\Filament\Resources\FaqArticles\Tables\FaqArticlesTable;
use App\Models\FaqArticle;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FaqArticleResource extends Resource
{
    protected static ?string $model = FaqArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return FaqArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqArticles::route('/'),
            'view' => ViewFaqArticle::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ticket', 'user']);
    }
}
