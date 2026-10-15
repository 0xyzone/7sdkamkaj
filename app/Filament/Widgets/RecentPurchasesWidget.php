<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPurchasesWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Purchases & Material Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Purchase::query()->latest('purchase_date'))
            ->paginated([5])
            ->columns([
                TextColumn::make('purchase_number')
                    ->label('Purchase Order')
                    ->weight('bold')
                    ->description(fn (Purchase $record): string => ($record->vendor?->name ?? 'No Vendor').' • '.$record->purchase_date?->format('M d, Y')),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('NPR')
                    ->weight('bold'),

                TextColumn::make('due_amount')
                    ->label('Due')
                    ->money('NPR')
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
                            ->label('Reference / Trx / Cheque #'),

                        FileUpload::make('receipt_image')
                            ->label('Receipt Photo')
                            ->image()
                            ->disk('public')
                            ->directory('purchase-payments'),

                        TextInput::make('notes')
                            ->label('Remarks'),
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
                            ->body('Remaining Due: NPR '.number_format((float) $record->due_amount, 2))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
