<?php

namespace App\Filament\Widgets;

use App\Models\ExpenseRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentExpensesWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Factory Expenses & Settlements';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ExpenseRecord::query()->latest('expense_date'))
            ->paginated([5])
            ->columns([
                ImageColumn::make('invoice_images')
                    ->label('Bill')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(1)
                    ->imageGallery(),

                TextColumn::make('expense_number')
                    ->label('Expense Ref')
                    ->weight('bold')
                    ->description(fn (ExpenseRecord $record): string => ($record->expenseType?->name ?? 'Expense').' • '.$record->expense_date?->format('M d, Y')),

                TextColumn::make('title')
                    ->label('Description')
                    ->limit(28)
                    ->description(fn (ExpenseRecord $record): ?string => $record->recipient_or_vendor ? "To: {$record->recipient_or_vendor}" : null),

                TextColumn::make('amount')
                    ->label('Incurred')
                    ->money('NPR')
                    ->weight('bold'),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('NPR')
                    ->color('success'),

                TextColumn::make('due_amount')
                    ->label('Due')
                    ->money('NPR')
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
            ])
            ->stackedOnMobile()
            ->recordActions([
                Action::make('record_payment')
                    ->label('Pay')
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
                            ->helperText(fn (ExpenseRecord $record): string => 'Outstanding balance: NPR '.number_format((float) $record->due_amount, 2)),

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
                            ->label('Reference / Trx #')
                            ->placeholder('Trx ID / Cheque #'),

                        FileUpload::make('receipt_image')
                            ->label('Voucher / Receipt')
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
            ]);
    }
}
