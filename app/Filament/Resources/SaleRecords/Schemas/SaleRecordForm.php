<?php

declare(strict_types=1);

namespace App\Filament\Resources\SaleRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SaleRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Sale Record Information')
                    ->tabs([
                        Tab::make('Sale & Bill Overview')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Section::make('Distributor & Bill Details')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('sale_number')
                                                    ->label('Sale Reference #')
                                                    ->disabled()
                                                    ->placeholder('Auto-generated (e.g. SALE-YYYYMM-0001)'),

                                                TextInput::make('bill_number')
                                                    ->label('Bill / Invoice Number')
                                                    ->placeholder('e.g. BILL-9821, INV-402')
                                                    ->maxLength(100),

                                                Select::make('distributor_id')
                                                    ->label('Distributor / Buyer')
                                                    ->relationship('distributor', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Select Distributor')
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Distributor / Company Name')
                                                            ->required(),
                                                        TextInput::make('pan_vat_number')
                                                            ->label('PAN / VAT Number'),
                                                        TextInput::make('phone')
                                                            ->label('Phone Number'),
                                                        TextInput::make('email')
                                                            ->label('Email Address')
                                                            ->email(),
                                                    ])
                                                    ->createOptionAction(fn ($action) => $action->slideOver()->modalWidth('xl')->modalHeading('Quick Add Distributor')),

                                                DatePicker::make('sale_date')
                                                    ->label('Sale Date')
                                                    ->default(now())
                                                    ->required(),

                                                TextInput::make('total_amount')
                                                    ->label('Total Sale / Bill Amount')
                                                    ->numeric()
                                                    ->prefix('NPR')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->placeholder('0.00'),

                                                Select::make('status')
                                                    ->label('Dispatch / Fulfillment Status')
                                                    ->options([
                                                        'completed' => 'Completed / Delivered',
                                                        'dispatched' => 'Dispatched / In Transit',
                                                        'pending' => 'Processing Order',
                                                        'cancelled' => 'Cancelled',
                                                    ])
                                                    ->default('completed')
                                                    ->required(),

                                                Select::make('payment_status')
                                                    ->label('Payment Settlement Status')
                                                    ->options([
                                                        'unpaid' => 'Unpaid (Due)',
                                                        'partial' => 'Partially Paid',
                                                        'paid' => 'Fully Paid',
                                                    ])
                                                    ->default('unpaid')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->helperText('Computed automatically from the Payment Records tab.'),

                                                Textarea::make('notes')
                                                    ->label('Dispatch / Order Notes')
                                                    ->rows(2)
                                                    ->placeholder('Item quantities, transport/courier receipt, terms, etc.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Bill & Goods Photos')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                Section::make('Official Bill & Invoice Copies')
                                    ->description('Upload scans or photos of the sales bill, challan, and tax invoices.')
                                    ->schema([
                                        FileUpload::make('bill_images')
                                            ->label('Bill / Invoice Photos')
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->directory('sales/bills')
                                            ->imageEditor()
                                            ->openable()
                                            ->downloadable()
                                            ->reorderable()
                                            ->maxSize(10240)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Dispatched Products / Goods Photos')
                                    ->description('Upload images of the finished jeans, cartons, packaging, or dispatch pallets.')
                                    ->schema([
                                        FileUpload::make('item_images')
                                            ->label('Product & Packaging Photos')
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->directory('sales/items')
                                            ->imageEditor()
                                            ->openable()
                                            ->downloadable()
                                            ->reorderable()
                                            ->maxSize(10240)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Payment Records')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->badge(fn ($record): ?string => $record && is_array($record->payments) ? (string) count($record->payments) : null)
                            ->schema([
                                Section::make('Received Payments & Receipts')
                                    ->description('Track individual payment instalments, bank deposits, cheques, and payment receipts.')
                                    ->schema([
                                        Repeater::make('payments')
                                            ->label('Payment Transactions')
                                            ->itemLabel(fn (array $state): ?string => isset($state['amount']) ? ('NPR '.number_format((float) $state['amount'], 2).' • '.($state['payment_method'] ?? 'Payment')) : 'New Payment')
                                            ->collapsible()
                                            ->cloneable()
                                            ->schema([
                                                Grid::make(['default' => 1, 'sm' => 3])
                                                    ->schema([
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
                                                            ->label('Payment Method')
                                                            ->options([
                                                                'cash' => 'Cash in Hand',
                                                                'bank_transfer' => 'Bank Transfer / ConnectIPS',
                                                                'cheque' => 'Bank Cheque',
                                                                'qr_code' => 'Fonepay / Bank QR',
                                                                'esewa' => 'eSewa Digital Wallet',
                                                                'khalti' => 'Khalti Digital Wallet',
                                                                'other' => 'Other Payment Mode',
                                                            ])
                                                            ->default('bank_transfer')
                                                            ->required(),
                                                    ]),

                                                Grid::make(['default' => 1, 'sm' => 2])
                                                    ->schema([
                                                        TextInput::make('reference_number')
                                                            ->label('Transaction Reference / Cheque #')
                                                            ->placeholder('e.g. CHQ-9012, Bank Ref #'),

                                                        TextInput::make('notes')
                                                            ->label('Payment Remarks / Note')
                                                            ->placeholder('e.g. 50% Advance received, Clearing instalment'),
                                                    ]),

                                                FileUpload::make('receipt_image')
                                                    ->label('Payment Receipt / Voucher Attachment')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('sales/receipts')
                                                    ->imageEditor()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->maxSize(10240)
                                                    ->helperText('Upload bank deposit slip, cheque photo, or payment screenshot.')
                                                    ->columnSpanFull(),
                                            ])
                                            ->addActionLabel('Log Payment Received')
                                            ->defaultItems(0),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
