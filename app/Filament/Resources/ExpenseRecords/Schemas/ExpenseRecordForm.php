<?php

namespace App\Filament\Resources\ExpenseRecords\Schemas;

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

class ExpenseRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Expense Information')
                    ->tabs([
                        Tab::make('Expense Details')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Section::make('Category & Overview')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('expense_number')
                                                    ->label('Expense Reference #')
                                                    ->disabled()
                                                    ->placeholder('Auto-generated (e.g. EXP-YYYYMM-0001)'),

                                                Select::make('expense_type_id')
                                                    ->label('Expense Category / Type')
                                                    ->relationship('expenseType', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Select Category (Salary, Rent, Groceries, etc.)')
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Category Name')
                                                            ->required(),
                                                        TextInput::make('code')
                                                            ->label('Short Code'),
                                                        Textarea::make('description')
                                                            ->label('Description'),
                                                    ])
                                                    ->createOptionAction(fn ($action) => $action->slideOver()->modalWidth('xl')->modalHeading('Quick Add Expense Category')),

                                                TextInput::make('title')
                                                    ->label('Expense Description / Item')
                                                    ->required()
                                                    ->placeholder('e.g. Monthly Factory Rent - Ashwin, Machine Needle Oil, Kitchen Veggies')
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                DatePicker::make('expense_date')
                                                    ->label('Expense Date')
                                                    ->default(now())
                                                    ->required(),

                                                TextInput::make('amount')
                                                    ->label('Total Incurred / Billed Amount')
                                                    ->numeric()
                                                    ->prefix('NPR')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->placeholder('0.00'),

                                                TextInput::make('recipient_or_vendor')
                                                    ->label('Paid To / Beneficiary / Shop')
                                                    ->placeholder('e.g. Landlord Name, Vendor, Master Cutter')
                                                    ->maxLength(255),

                                                TextInput::make('invoice_number')
                                                    ->label('Bill / Invoice # (Optional)')
                                                    ->placeholder('e.g. INV-9821')
                                                    ->maxLength(100),

                                                Grid::make(['default' => 1, 'sm' => 2])
                                                    ->schema([
                                                        TextInput::make('quantity')
                                                            ->label('Quantity (Optional)')
                                                            ->numeric()
                                                            ->placeholder('e.g. 5, 1, 100'),

                                                        Select::make('unit')
                                                            ->label('Unit of Measurement')
                                                            ->options([
                                                                'pcs' => 'Pieces (pcs)',
                                                                'kg' => 'Kilograms (kg)',
                                                                'ltr' => 'Litres (ltr)',
                                                                'month' => 'Month',
                                                                'day' => 'Days / Shifts',
                                                                'person' => 'Persons / Staff',
                                                                'box' => 'Boxes',
                                                                'bundle' => 'Bundles',
                                                                'm' => 'Meters',
                                                                'cylinder' => 'Cylinders (LPG)',
                                                                'trip' => 'Trips (Logistics)',
                                                                'other' => 'Other / General',
                                                            ])
                                                            ->searchable()
                                                            ->placeholder('Select or type unit'),
                                                    ])
                                                    ->columnSpanFull(),

                                                Select::make('payment_status')
                                                    ->label('Settlement Status')
                                                    ->options([
                                                        'unpaid' => 'Unpaid (Pending Settlement)',
                                                        'partial' => 'Partially Paid',
                                                        'paid' => 'Fully Settled / Paid',
                                                    ])
                                                    ->default('unpaid')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->helperText('Automatically calculated based on recorded payments in the Payments tab.')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Payments Made')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->badge(fn ($record): ?string => $record && is_array($record->payments) ? (string) count($record->payments) : null)
                            ->schema([
                                Section::make('Actual Cash Outflow Log')
                                    ->description('💡 Log actual monetary payouts made for this expense. Only payments recorded here count toward factory cash spending.')
                                    ->schema([
                                        Repeater::make('payments')
                                            ->label('Payment Transactions')
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(fn (array $state): ?string => filled($state['amount'] ?? null)
                                                ? 'NPR '.number_format((float) $state['amount'], 2).' via '.ucfirst($state['payment_method'] ?? 'cash')
                                                : 'Payment Entry')
                                            ->schema([
                                                Grid::make(['default' => 1, 'sm' => 3])
                                                    ->schema([
                                                        DatePicker::make('payment_date')
                                                            ->label('Payment Date')
                                                            ->default(now())
                                                            ->required(),

                                                        TextInput::make('amount')
                                                            ->label('Amount Paid (NPR)')
                                                            ->numeric()
                                                            ->prefix('NPR')
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
                                                            ->default('cash')
                                                            ->required(),

                                                        TextInput::make('reference_number')
                                                            ->label('Reference / Cheque / Trx #')
                                                            ->placeholder('Cheque # or Trx ID'),

                                                        FileUpload::make('receipt_image')
                                                            ->label('Payment Receipt / Voucher Photo')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('expense-payments')
                                                            ->openable()
                                                            ->downloadable()
                                                            ->previewable(),

                                                        TextInput::make('notes')
                                                            ->label('Payment Remarks')
                                                            ->placeholder('e.g. Advance / Final payment'),
                                                    ]),
                                            ])
                                            ->addActionLabel('Record New Payment')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('Photos & Bills')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                Section::make('Invoices, Bills & Receipts')
                                    ->description('Upload photographs or scans of the physical invoice, cash memo, or tax bill.')
                                    ->schema([
                                        FileUpload::make('invoice_images')
                                            ->label('Bill / Invoice Photos')
                                            ->multiple()
                                            ->image()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('expense-invoices')
                                            ->openable()
                                            ->downloadable()
                                            ->previewable()
                                            ->panelLayout('grid')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Items & Materials Purchased')
                                    ->description('Photographs of the physical items, groceries, supplies, or machinery parts acquired.')
                                    ->schema([
                                        FileUpload::make('item_images')
                                            ->label('Item / Product Photos')
                                            ->multiple()
                                            ->image()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('expense-items')
                                            ->openable()
                                            ->downloadable()
                                            ->previewable()
                                            ->panelLayout('grid')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Remarks')
                            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                            ->schema([
                                Section::make('Additional Remarks & Notes')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Internal Notes')
                                            ->placeholder('Add any special context, approvals, or operational details...')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
