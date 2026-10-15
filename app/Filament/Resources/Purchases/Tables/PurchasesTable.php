<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Models\Purchase;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_number')
                    ->label('Purchase Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Purchase $record): string => ($record->vendor?->name ?? 'No Vendor').' • '.$record->purchase_date?->format('M d, Y')),

                TextColumn::make('invoice_number')
                    ->label('Bill / Inv #')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_amount')
                    ->label('Total (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('paid_amount')
                    ->label('Paid (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('due_amount')
                    ->label('Due (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->label('Delivery')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'received' => 'success',
                        'pending' => 'warning',
                        'ordered' => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('purchase_date', 'desc')
            ->filters([
                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ]),

                SelectFilter::make('status')
                    ->label('Order Status')
                    ->options([
                        'received' => 'Goods Received',
                        'pending' => 'Pending Delivery',
                        'ordered' => 'Ordered',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('recordPayment')
                    ->label('Pay')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->slideOver()
                    ->modalHeading(fn (Purchase $record): string => "Record Payment for {$record->purchase_number}")
                    ->modalDescription(fn (Purchase $record): string => 'Current Due: NPR '.number_format((float) $record->due_amount, 2))
                    ->visible(fn (Purchase $record): bool => (float) $record->due_amount > 0)
                    ->form([
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount to Pay (NPR)')
                            ->numeric()
                            ->prefix('NPR')
                            ->default(fn (Purchase $record): float => (float) $record->due_amount)
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'esewa' => 'eSewa',
                                'khalti' => 'Khalti',
                                'fonepay' => 'Fonepay / Bank QR',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Bank Cheque',
                                'connectips' => 'ConnectIPS',
                                'other' => 'Other',
                            ])
                            ->default('bank_transfer')
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference / Trx / Cheque #')
                            ->placeholder('e.g. Trx-98124 or Cheque-0012'),

                        FileUpload::make('receipt_image')
                            ->label('Receipt / Voucher Photo')
                            ->image()
                            ->disk('public')
                            ->directory('purchase-payments'),

                        TextInput::make('notes')
                            ->label('Payment Notes / Remarks')
                            ->placeholder('e.g. Part payment via mobile banking'),
                    ])
                    ->action(function (Purchase $record, array $data): void {
                        $payments = is_array($record->payments) ? $record->payments : [];
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
                            ->title('Payment recorded successfully')
                            ->body('NPR '.number_format((float) $data['amount'], 2)." credited to {$record->purchase_number}. Remaining Due: NPR ".number_format((float) $record->due_amount, 2))
                            ->success()
                            ->send();
                    }),

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
