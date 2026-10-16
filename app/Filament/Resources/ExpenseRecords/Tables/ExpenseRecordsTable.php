<?php

namespace App\Filament\Resources\ExpenseRecords\Tables;

use App\Models\ExpenseRecord;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('invoice_images')
                    ->label('Bills')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->imageGallery(),

                TextColumn::make('expense_number')
                    ->label('Expense Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (ExpenseRecord $record): string => ($record->expenseType?->name ?? 'Uncategorized').' • '.$record->expense_date?->format('M d, Y')),

                TextColumn::make('title')
                    ->label('Description')
                    ->searchable()
                    ->limit(35)
                    ->description(fn (ExpenseRecord $record): ?string => $record->recipient_or_vendor ? "Paid to: {$record->recipient_or_vendor}" : null),

                TextColumn::make('amount')
                    ->label('Incurred (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('paid_amount')
                    ->label('Paid (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->color('success')
                    ->description(fn (ExpenseRecord $record): string => (float) $record->amount > 0 ? round(((float) $record->paid_amount / (float) $record->amount) * 100).'% paid' : '0%'),

                TextColumn::make('due_amount')
                    ->label('Due (NPR)')
                    ->money('NPR')
                    ->sortable()
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('quantity_with_unit')
                    ->label('Qty / Unit')
                    ->state(function (ExpenseRecord $record): ?string {
                        if (! $record->quantity) {
                            return $record->unit ? ucfirst($record->unit) : null;
                        }
                        $qty = (float) $record->quantity;

                        return $record->unit ? "{$qty} {$record->unit}" : (string) $qty;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('invoice_number')
                    ->label('Bill #')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('item_images')
                    ->label('Item Photos')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->imageGallery()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->stackedOnMobile()
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('expense_type_id')
                    ->label('Category')
                    ->relationship('expenseType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'unpaid' => 'Unpaid (Due)',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ]),

                Filter::make('expense_date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('record_payment')
                    ->label('Log Payment')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->visible(fn (ExpenseRecord $record): bool => (float) $record->due_amount > 0)
                    ->form([
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Payment Amount (NPR)')
                            ->numeric()
                            ->prefix('NPR')
                            ->required()
                            ->default(fn (ExpenseRecord $record): float => (float) $record->due_amount)
                            ->helperText(fn (ExpenseRecord $record): string => 'Outstanding balance due: NPR '.number_format((float) $record->due_amount, 2)),

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
                            ->default('cash')
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference / Cheque / Trx #')
                            ->placeholder('e.g. Trx-91823'),

                        FileUpload::make('receipt_image')
                            ->label('Payment Voucher / Receipt')
                            ->image()
                            ->disk('public')
                            ->directory('expense-payments'),

                        TextInput::make('notes')
                            ->label('Remarks'),
                    ])
                    ->action(function (ExpenseRecord $record, array $data): void {
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
                            ->body('Payment of NPR '.number_format((float) $data['amount'], 2).' applied to '.$record->expense_number)
                            ->success()
                            ->send();
                    })
                    ->slideOver()
                    ->modalWidth('xl'),

                EditAction::make()->slideOver()->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
