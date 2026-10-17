<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Distributor;
use App\Models\SaleRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaleStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalSales = (float) SaleRecord::sum('total_amount');
        $totalCollected = (float) SaleRecord::sum('paid_amount');
        $totalDue = (float) SaleRecord::sum('due_amount');
        $totalCount = SaleRecord::count();
        $unpaidCount = SaleRecord::where('payment_status', 'unpaid')->count();
        $partialCount = SaleRecord::where('payment_status', 'partial')->count();
        $paidCount = SaleRecord::where('payment_status', 'paid')->count();
        $distributorCount = Distributor::where('is_active', true)->count();

        $collectionPercent = $totalSales > 0 ? round(($totalCollected / $totalSales) * 100, 1) : 0;

        return [
            Stat::make('Total Sales Billed', 'NPR '.number_format($totalSales, 2))
                ->description($totalCount.' sales orders billed')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Cash Collected (Paid)', 'NPR '.number_format($totalCollected, 2))
                ->description($collectionPercent.'% collected from distributors')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Outstanding Due (Receivables)', 'NPR '.number_format($totalDue, 2))
                ->description(($unpaidCount + $partialCount).' invoices with pending balance')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($totalDue > 0 ? 'danger' : 'gray'),

            Stat::make('Active Distributors', (string) $distributorCount)
                ->description($paidCount.' fully settled sales orders')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),
        ];
    }
}
