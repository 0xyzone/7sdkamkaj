<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DistributorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Distributor / Company')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(function ($record): ?string {
                        $parts = [];
                        if ($record->code) {
                            $parts[] = 'Code: '.$record->code;
                        }
                        if ($record->pan_vat_number) {
                            $parts[] = 'PAN/VAT: '.$record->pan_vat_number;
                        }
                        if ($record->address) {
                            $parts[] = $record->address;
                        }

                        return count($parts) ? implode(' • ', $parts) : null;
                    }),

                TextColumn::make('contacts_count')
                    ->label('Key Contact')
                    ->state(function ($record): ?string {
                        if (is_array($record->contacts) && count($record->contacts)) {
                            $first = $record->contacts[0];
                            $name = $first['contact_name'] ?? '';
                            $role = ! empty($first['designation']) ? ' ('.$first['designation'].')' : '';

                            return $name.$role;
                        }

                        return 'None attached';
                    })
                    ->description(function ($record): ?string {
                        if (is_array($record->contacts) && count($record->contacts)) {
                            $first = $record->contacts[0];
                            if (! empty($first['phone_numbers']) && is_array($first['phone_numbers'])) {
                                return $first['phone_numbers'][0]['number'] ?? null;
                            }
                        }

                        return $record->phone;
                    })
                    ->badge(fn ($state) => $state === 'None attached' ? 'gray' : null),

                TextColumn::make('sales_count')
                    ->counts('sales')
                    ->label('Sales Records')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('sales_sum_total_amount')
                    ->sum('sales', 'total_amount')
                    ->label('Total Revenue')
                    ->money('NPR')
                    ->weight(FontWeight::Medium)
                    ->sortable(),

                TextColumn::make('sales_sum_due_amount')
                    ->sum('sales', 'due_amount')
                    ->label('Pending Due')
                    ->money('NPR')
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'gray')
                    ->weight(FontWeight::SemiBold)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->stackedOnMobile()
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active Distributors')
                    ->falseLabel('Inactive Distributors'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
