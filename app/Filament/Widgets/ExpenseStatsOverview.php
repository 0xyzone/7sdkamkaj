<?php

namespace App\Filament\Widgets;

use App\Models\ExpenseRecord;
use App\Models\ExpenseType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalIncurred = (float) ExpenseRecord::sum('amount');
        $totalPaid = (float) ExpenseRecord::sum('paid_amount');
        $totalDue = (float) ExpenseRecord::sum('due_amount');
        $totalCount = ExpenseRecord::count();
        $unpaidCount = ExpenseRecord::where('payment_status', 'unpaid')->count();
        $partialCount = ExpenseRecord::where('payment_status', 'partial')->count();
        $paidCount = ExpenseRecord::where('payment_status', 'paid')->count();
        $typeCount = ExpenseType::where('is_active', true)->count();

        $settlementPercent = $totalIncurred > 0 ? round(($totalPaid / $totalIncurred) * 100, 1) : 0;

        return [
            Stat::make('Expenses Incurred / Billed', 'NPR '.number_format($totalIncurred, 2))
                ->description($totalCount.' records (Liabilities booked)')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Actual Cash Spent (Paid)', 'NPR '.number_format($totalPaid, 2))
                ->description($settlementPercent.'% of total expenses paid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Payable (Due)', 'NPR '.number_format($totalDue, 2))
                ->description(($unpaidCount + $partialCount).' records awaiting payment')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalDue > 0 ? 'danger' : 'gray'),

            Stat::make('Active Categories', (string) $typeCount)
                ->description($paidCount.' fully settled records')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
        ];
    }
}
