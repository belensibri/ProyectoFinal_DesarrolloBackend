<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', Ticket::count())
                ->description('Todos los tickets registrados')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
            
            Stat::make('Tickets Activos', Ticket::where('estado', Ticket::ESTADO_ACTIVO)->count())
                ->description('Pendientes de atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('Tickets en Progreso', Ticket::where('estado', Ticket::ESTADO_EN_PROCESO)->count())
                ->description('Actualmente en atención')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Tickets Cerrados', Ticket::where('estado', Ticket::ESTADO_CERRADO)->count())
                ->description('Casos resueltos')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
