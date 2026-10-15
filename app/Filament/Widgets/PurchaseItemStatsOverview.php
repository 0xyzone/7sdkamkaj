<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseItemStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $totalItems = PurchaseItem::count();
        $activeItems = PurchaseItem::where('is_active', true)->count();
        $fabrics = PurchaseItem::where('category', 'Fabric / Denim')->count();
        $trims = PurchaseItem::where('category', 'Trims & Hardware')->count();

        return [
            Stat::make('Total Materials', (string) $totalItems)
                ->description($activeItems.' active for procurement')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Denim & Fabrics', (string) $fabrics)
                ->description('Fabric catalog entries')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make('Trims & Hardware', (string) $trims)
                ->description('Buttons, zippers, and rivets')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),
        ];
    }
}
