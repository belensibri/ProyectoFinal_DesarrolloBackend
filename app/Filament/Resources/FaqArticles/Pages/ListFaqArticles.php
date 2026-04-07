<?php

namespace App\Filament\Resources\FaqArticles\Pages;

use App\Filament\Resources\FaqArticles\FaqArticleResource;
use Filament\Resources\Pages\ListRecords;

class ListFaqArticles extends ListRecords
{
    protected static string $resource = FaqArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
