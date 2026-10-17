<?php

declare(strict_types=1);

namespace App\Filament\Resources\SaleRecords\Pages;

use App\Filament\Resources\SaleRecords\SaleRecordResource;
use App\Filament\Widgets\SaleStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaleRecords extends ListRecords
{
    protected static string $resource = SaleRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalWidth('7xl'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SaleStatsOverview::class,
        ];
    }
}
