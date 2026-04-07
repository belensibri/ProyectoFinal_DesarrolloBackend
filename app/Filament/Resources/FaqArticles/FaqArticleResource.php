<?php

namespace App\Filament\Resources\FaqArticles;

use App\Filament\Resources\FaqArticles\Pages\CreateFaqArticle;
use App\Filament\Resources\FaqArticles\Pages\EditFaqArticle;
use App\Filament\Resources\FaqArticles\Pages\ListFaqArticles;
use App\Filament\Resources\FaqArticles\Schemas\FaqArticleForm;
use App\Filament\Resources\FaqArticles\Tables\FaqArticlesTable;
use App\Models\FaqArticle;
use BackedEnum;
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
        return FaqArticleForm::configure($schema);
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
        ];
    }
}
