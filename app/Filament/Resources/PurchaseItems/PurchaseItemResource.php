<?php

namespace App\Filament\Resources\PurchaseItems;

use App\Filament\Resources\PurchaseItems\Pages\CreatePurchaseItem;
use App\Filament\Resources\PurchaseItems\Pages\EditPurchaseItem;
use App\Filament\Resources\PurchaseItems\Pages\ListPurchaseItems;
use App\Filament\Resources\PurchaseItems\Schemas\PurchaseItemForm;
use App\Filament\Resources\PurchaseItems\Tables\PurchaseItemsTable;
use App\Models\PurchaseItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseItemResource extends Resource
{
    protected static ?string $model = PurchaseItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Purchase';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return PurchaseItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseItemsTable::configure($table);
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
            'index' => ListPurchaseItems::route('/'),
            'create' => CreatePurchaseItem::route('/create'),
            'edit' => EditPurchaseItem::route('/{record}/edit'),
        ];
    }
}
