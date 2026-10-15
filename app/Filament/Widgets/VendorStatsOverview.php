<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('is_active', true)->count();
        $dueAmount = (float) Purchase::sum('due_amount');
        $vendorsWithDue = Purchase::where('due_amount', '>', 0)->distinct('vendor_id')->count('vendor_id');

        return [
            Stat::make('Total Vendors', (string) $totalVendors)
                ->description($activeVendors.' active suppliers')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Vendors with Due', (string) $vendorsWithDue)
                ->description('Suppliers awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color($vendorsWithDue > 0 ? 'warning' : 'success'),

            Stat::make('Total Outstanding Payable', 'NPR '.number_format($dueAmount, 2))
                ->description('Total pending liabilities')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($dueAmount > 0 ? 'danger' : 'gray'),
        ];
    }
}
