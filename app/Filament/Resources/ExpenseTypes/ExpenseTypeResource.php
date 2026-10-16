<?php

namespace App\Filament\Resources\ExpenseTypes;

use App\Filament\Resources\ExpenseTypes\Pages\ListExpenseTypes;
use App\Filament\Resources\ExpenseTypes\Schemas\ExpenseTypeForm;
use App\Filament\Resources\ExpenseTypes\Tables\ExpenseTypesTable;
use App\Models\ExpenseType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExpenseTypeResource extends Resource
{
    protected static ?string $model = ExpenseType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Expenses';

    protected static ?string $navigationLabel = 'Expense Categories';

    protected static ?string $modelLabel = 'Expense Category';

    protected static ?string $pluralModelLabel = 'Expense Categories';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return ExpenseTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenseTypes::route('/'),
        ];
    }
}
