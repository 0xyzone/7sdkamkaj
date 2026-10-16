<?php

namespace App\Filament\Resources\ExpenseRecords;

use App\Filament\Resources\ExpenseRecords\Pages\ListExpenseRecords;
use App\Filament\Resources\ExpenseRecords\Schemas\ExpenseRecordForm;
use App\Filament\Resources\ExpenseRecords\Tables\ExpenseRecordsTable;
use App\Models\ExpenseRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExpenseRecordResource extends Resource
{
    protected static ?string $model = ExpenseRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static string|UnitEnum|null $navigationGroup = 'Expenses';

    protected static ?string $navigationLabel = 'Expense Records';

    protected static ?string $modelLabel = 'Expense Record';

    protected static ?string $pluralModelLabel = 'Expense Records';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return ExpenseRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseRecordsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenseRecords::route('/'),
        ];
    }
}
