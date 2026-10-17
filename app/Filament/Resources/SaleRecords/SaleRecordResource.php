<?php

declare(strict_types=1);

namespace App\Filament\Resources\SaleRecords;

use App\Filament\Resources\SaleRecords\Pages\ListSaleRecords;
use App\Filament\Resources\SaleRecords\Schemas\SaleRecordForm;
use App\Filament\Resources\SaleRecords\Tables\SaleRecordsTable;
use App\Models\SaleRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SaleRecordResource extends Resource
{
    protected static ?string $model = SaleRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $unpaidCount = static::getModel()::where('payment_status', '!=', 'paid')->count();

        return $unpaidCount > 0 ? (string) $unpaidCount : (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $unpaidCount = static::getModel()::where('payment_status', '!=', 'paid')->count();

        return $unpaidCount > 0 ? 'danger' : 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return SaleRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaleRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleRecords::route('/'),
        ];
    }
}
