<?php

namespace App\Filament\Resources\FaqArticles\Pages;

use App\Filament\Resources\FaqArticles\FaqArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqArticle extends CreateRecord
{
    protected static string $resource = FaqArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return FaqArticleResource::getUrl('index');
    }
}
