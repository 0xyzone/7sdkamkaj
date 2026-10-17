<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors;

use App\Filament\Resources\Distributors\Pages\ListDistributors;
use App\Filament\Resources\Distributors\Schemas\DistributorForm;
use App\Filament\Resources\Distributors\Tables\DistributorsTable;
use App\Models\Distributor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DistributorResource extends Resource
{
    protected static ?string $model = Distributor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return DistributorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DistributorsTable::configure($table);
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
            'index' => ListDistributors::route('/'),
        ];
    }
}
