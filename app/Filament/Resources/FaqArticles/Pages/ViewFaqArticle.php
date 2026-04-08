<?php

namespace App\Filament\Resources\FaqArticles\Pages;

use App\Filament\Resources\FaqArticles\FaqArticleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewFaqArticle extends ViewRecord
{
    protected static string $resource = FaqArticleResource::class;

    protected string $view = 'filament.resources.faq-articles.pages.view-faq-article';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'user',
            'ticket.user',
            'ticket.technician',
            'ticket.comments.user',
            'ticket.comments.attachments',
            'ticket.ticketHistories.user',
        ]);
    }

    public function getCategoryLabel(): string
    {
        return str((string) $this->record->categoria)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    public function getResolutionTypeLabel(): string
    {
        return match ($this->record->tipo_resolucion) {
            'workaround' => 'Workaround',
            'solucion_definitiva' => 'Solucion definitiva',
            default => str((string) $this->record->tipo_resolucion)->headline()->toString(),
        };
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
