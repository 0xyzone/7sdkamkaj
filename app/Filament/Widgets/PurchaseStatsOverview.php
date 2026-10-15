<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPurchases = (float) Purchase::sum('total_amount');
        $totalPaid = (float) Purchase::sum('paid_amount');
        $totalDue = (float) Purchase::sum('due_amount');
        $orderCount = Purchase::count();
        $dueCount = Purchase::where('due_amount', '>', 0)->count();
        $vendorCount = Vendor::where('is_active', true)->count();

        return [
            Stat::make('Total Purchases', 'NPR '.number_format($totalPurchases, 2))
                ->description($orderCount.' total orders recorded')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Total Paid', 'NPR '.number_format($totalPaid, 2))
                ->description('Settled vendor payments')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Outstanding Due', 'NPR '.number_format($totalDue, 2))
                ->description($dueCount.' purchases with due balance')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($totalDue > 0 ? 'danger' : 'gray'),

            Stat::make('Active Vendors', (string) $vendorCount)
                ->description('Registered suppliers')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),
        ];
    }
}
