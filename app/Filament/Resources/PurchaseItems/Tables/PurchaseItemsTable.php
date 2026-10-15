<?php

namespace App\Filament\Resources\PurchaseItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PurchaseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Preview')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->disk('public')
                    ->imageGallery(),

                TextColumn::make('name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => ($record->vendor?->name ? 'Vendor: '.$record->vendor->name : null)
                        .($record->cost_price ? ' • NPR '.number_format($record->cost_price, 2) : '')),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Uncategorized'),

                TextColumn::make('unit_of_measurement')
                    ->label('UOM')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A'),

                TextColumn::make('cost_price')
                    ->label('Est. Cost')
                    ->money('NPR')
                    ->sortable()
                    ->placeholder('0.00')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('vendor.name')
                    ->label('Preferred Vendor')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->placeholder('None Assigned')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Fabric / Denim' => 'Fabric / Denim',
                        'Trims & Hardware' => 'Trims & Hardware (Zippers, Buttons, Rivets)',
                        'Thread & Yarn' => 'Thread & Yarn',
                        'Labels & Tags' => 'Labels & Tags',
                        'Pocketing & Lining' => 'Pocketing & Lining',
                        'Accessories & Elastic' => 'Accessories & Elastic',
                        'Packaging Materials' => 'Packaging Materials',
                        'Chemicals & Washes' => 'Chemicals & Washes',
                        'Other Raw Material' => 'Other Raw Material',
                    ]),

                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Items')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
