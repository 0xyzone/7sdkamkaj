<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->pan_vat_number ? 'PAN: '.$record->pan_vat_number : ($record->email ?: null)),

                TextColumn::make('pan_vat_number')
                    ->label('PAN / VAT')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contacts')
                    ->label('Contacts')
                    ->state(fn ($record): string => is_array($record?->contacts) && count($record->contacts) > 0
                        ? count($record->contacts).' Person(s)'
                        : 'No Contacts')
                    ->badge()
                    ->color(fn ($record): string => is_array($record?->contacts) && count($record->contacts) > 0 ? 'info' : 'gray'),

                ImageColumn::make('qrs')
                    ->label('QRs')
                    ->disk('public')
                    ->state(fn ($record) => collect((array) ($record->qrs ?? []))->pluck('qr_image')->filter()->values()->all())
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->imageGallery()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bank_accounts')
                    ->label('Banks')
                    ->state(fn ($record): string => is_array($record?->bank_accounts) && count($record->bank_accounts) > 0
                        ? count($record->bank_accounts).' Account(s)'
                        : 'None')
                    ->badge()
                    ->color(fn ($record): string => is_array($record?->bank_accounts) && count($record->bank_accounts) > 0 ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Vendors')
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
