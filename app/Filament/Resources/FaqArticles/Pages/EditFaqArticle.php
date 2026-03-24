<?php

namespace App\Filament\Resources\FaqArticles\Pages;

use App\Filament\Resources\FaqArticles\FaqArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFaqArticle extends EditRecord
{
    protected static string $resource = FaqArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
