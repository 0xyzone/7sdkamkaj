<?php

declare(strict_types=1);

namespace App\Filament\Resources\SaleRecords\Tables;

use App\Models\SaleRecord;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaleRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_number')
                    ->label('Sale / Bill #')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (SaleRecord $record): ?string => $record->bill_number ? 'Bill: '.$record->bill_number : null),

                TextColumn::make('distributor.name')
                    ->label('Distributor')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable()
                    ->description(fn (SaleRecord $record): ?string => $record->distributor?->phone ?? $record->distributor?->pan_vat_number),

                TextColumn::make('sale_date')
                    ->label('Sale Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Sale')
                    ->money('NPR')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Collected')
                    ->money('NPR')
                    ->color('success')
                    ->weight(FontWeight::Medium)
                    ->sortable(),

                TextColumn::make('due_amount')
                    ->label('Due Balance')
                    ->money('NPR')
                    ->color(fn (SaleRecord $record): string => (float) $record->due_amount > 0 ? 'danger' : 'gray')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'unpaid' => 'Unpaid',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'completed',
                        'info' => 'dispatched',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completed',
                        'dispatched' => 'Dispatched',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),

                ImageColumn::make('bill_images')
                    ->label('Bill Copy')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText()
                    ->disk('public'),
            ])
            ->defaultSort('sale_date', 'desc')
            ->stackedOnMobile()
            ->filters([
                SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'unpaid' => 'Unpaid (Due)',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ]),

                SelectFilter::make('status')
                    ->label('Fulfillment Status')
                    ->options([
                        'completed' => 'Completed / Delivered',
                        'dispatched' => 'Dispatched / In Transit',
                        'pending' => 'Pending Order',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('logPayment')
                    ->label('Log Payment')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->visible(fn (SaleRecord $record): bool => $record->payment_status !== 'paid')
                    ->fillForm(fn (SaleRecord $record): array => [
                        'amount' => $record->due_amount > 0 ? $record->due_amount : null,
                        'payment_date' => now()->toDateString(),
                    ])
                    ->form([
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount Received')
                            ->numeric()
                            ->prefix('NPR')
                            ->required()
                            ->placeholder('0.00'),

                        Select::make('payment_method')
                            ->label('Payment Mode')
                            ->options([
                                'bank_transfer' => 'Bank Transfer / ConnectIPS',
                                'cash' => 'Cash in Hand',
                                'cheque' => 'Bank Cheque',
                                'qr_code' => 'Fonepay / Bank QR',
                                'esewa' => 'eSewa',
                                'khalti' => 'Khalti',
                                'other' => 'Other',
                            ])
                            ->default('bank_transfer')
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference / Cheque / Trx #')
                            ->placeholder('e.g. Trx-91823, Cheque #09123'),

                        FileUpload::make('receipt_image')
                            ->label('Payment Voucher / Receipt')
                            ->image()
                            ->disk('public')
                            ->directory('sales/receipts'),

                        TextInput::make('notes')
                            ->label('Remarks / Note'),
                    ])
                    ->action(function (SaleRecord $record, array $data): void {
                        $payments = (array) ($record->payments ?? []);
                        $payments[] = [
                            'payment_date' => $data['payment_date'],
                            'amount' => (float) $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'reference_number' => $data['reference_number'] ?? null,
                            'receipt_image' => $data['receipt_image'] ?? null,
                            'notes' => $data['notes'] ?? null,
                        ];

                        $record->payments = $payments;
                        $record->save();

                        Notification::make()
                            ->title('Payment Logged Successfully')
                            ->body('Payment of NPR '.number_format((float) $data['amount'], 2).' credited to '.$record->sale_number)
                            ->success()
                            ->send();
                    })
                    ->slideOver()
                    ->modalWidth('xl'),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
