<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', Ticket::count())
                ->description('Todos los tickets registrados')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
            
            Stat::make('Tickets Abiertos', Ticket::where('estado', 'abierto')->count())
                ->description('Pendientes de atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Tickets en Progreso', Ticket::where('estado', 'en_progreso')->count())
                ->description('Actualmente en atención')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
