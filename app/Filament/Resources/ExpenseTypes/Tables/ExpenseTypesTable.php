<?php

namespace App\Filament\Resources\ExpenseTypes\Tables;

use App\Models\ExpenseType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExpenseTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (ExpenseType $record): ?string => $record->description),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('expense_records_count')
                    ->counts('expenseRecords')
                    ->label('Recorded Expenses')
                    ->badge()
                    ->color('primary'),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->stackedOnMobile()
            ->defaultSort('name', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active Categories')
                    ->falseLabel('Inactive Categories'),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('2xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
