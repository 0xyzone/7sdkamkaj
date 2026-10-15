<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\PurchaseItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Purchase Details')
                    ->tabs([
                        Tab::make('Order Information')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Section::make('Vendor & Bill Reference')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('purchase_number')
                                                    ->label('Purchase Order #')
                                                    ->disabled()
                                                    ->placeholder('Auto-generated upon save (e.g. PO-YYYYMM-0001)'),

                                                TextInput::make('invoice_number')
                                                    ->label('Vendor Bill / Invoice #')
                                                    ->placeholder('e.g. INV-9821')
                                                    ->maxLength(100),

                                                Select::make('vendor_id')
                                                    ->label('Vendor')
                                                    ->relationship('vendor', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Select Vendor')
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Vendor Name')
                                                            ->required(),
                                                        TextInput::make('pan_vat_number')
                                                            ->label('PAN / VAT'),
                                                        TextInput::make('email')
                                                            ->email(),
                                                    ])
                                                    ->createOptionAction(fn ($action) => $action->slideOver()->modalHeading('Quick Add Vendor')),

                                                DatePicker::make('purchase_date')
                                                    ->label('Purchase Date')
                                                    ->default(now())
                                                    ->required(),

                                                Select::make('status')
                                                    ->label('Delivery / Order Status')
                                                    ->options([
                                                        'received' => 'Goods Received',
                                                        'pending' => 'Pending Delivery',
                                                        'ordered' => 'Ordered / Placed',
                                                        'cancelled' => 'Cancelled',
                                                    ])
                                                    ->default('received')
                                                    ->required(),

                                                Select::make('payment_status')
                                                    ->label('Payment Status')
                                                    ->options([
                                                        'unpaid' => 'Unpaid',
                                                        'partial' => 'Partially Paid',
                                                        'paid' => 'Fully Paid',
                                                    ])
                                                    ->default('unpaid')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->helperText('Automatically computed based on recorded payments.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Purchased Items')
                            ->icon(Heroicon::OutlinedArchiveBox)
                            ->badge(fn ($record): ?string => $record && is_array($record->items) ? (string) count($record->items) : null)
                            ->schema([
                                Repeater::make('items')
                                    ->label('Purchase Line Items')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => filled($state['item_name'] ?? null)
                                        ? "{$state['item_name']} (".($state['quantity'] ?? 0).' '.($state['unit_of_measurement'] ?? '').')'
                                        : 'Purchase Item')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 12])
                                            ->schema([
                                                Select::make('purchase_item_id')
                                                    ->label('Catalog Item (Optional Selection)')
                                                    ->options(fn () => PurchaseItem::where('is_active', true)->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set) {
                                                        if (! $state) {
                                                            return;
                                                        }
                                                        $catalogItem = PurchaseItem::find($state);
                                                        if ($catalogItem) {
                                                            $set('item_name', $catalogItem->name);
                                                            $set('unit_of_measurement', $catalogItem->unit_of_measurement);
                                                            if ($catalogItem->cost_price) {
                                                                $set('unit_price', $catalogItem->cost_price);
                                                            }
                                                        }
                                                    })
                                                    ->columnSpan(['default' => 12, 'sm' => 4]),

                                                TextInput::make('item_name')
                                                    ->label('Item / Material Description')
                                                    ->required()
                                                    ->placeholder('Item name')
                                                    ->columnSpan(['default' => 12, 'sm' => 4]),

                                                TextInput::make('quantity')
                                                    ->label('Quantity')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live(debounce: 300)
                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                        $qty = (float) ($get('quantity') ?? 0);
                                                        $price = (float) ($get('unit_price') ?? 0);
                                                        $set('subtotal', round($qty * $price, 2));
                                                    })
                                                    ->columnSpan(['default' => 6, 'sm' => 2]),

                                                TextInput::make('unit_of_measurement')
                                                    ->label('UOM')
                                                    ->placeholder('Meters, Pcs, etc.')
                                                    ->columnSpan(['default' => 6, 'sm' => 2]),

                                                TextInput::make('unit_price')
                                                    ->label('Unit Price (NPR)')
                                                    ->numeric()
                                                    ->prefix('NPR')
                                                    ->required()
                                                    ->live(debounce: 300)
                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                        $qty = (float) ($get('quantity') ?? 0);
                                                        $price = (float) ($get('unit_price') ?? 0);
                                                        $set('subtotal', round($qty * $price, 2));
                                                    })
                                                    ->columnSpan(['default' => 6, 'sm' => 3]),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal (NPR)')
                                                    ->numeric()
                                                    ->prefix('NPR')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->columnSpan(['default' => 6, 'sm' => 3]),

                                                TextInput::make('item_notes')
                                                    ->label('Notes / Roll Numbers / Color')
                                                    ->placeholder('Optional item notes')
                                                    ->columnSpan(['default' => 12, 'sm' => 6]),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Purchased Item')
                                    ->defaultItems(1),
                            ]),

                        Tab::make('Payments')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->badge(fn ($record): ?string => $record && is_array($record->payments) ? (string) count($record->payments) : null)
                            ->schema([
                                Repeater::make('payments')
                                    ->label('Payments Made for this Purchase')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => filled($state['amount'] ?? null)
                                        ? 'NPR '.number_format((float) $state['amount'], 2).' via '.ucfirst($state['payment_method'] ?? 'cash')
                                        : 'Payment Entry')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                DatePicker::make('payment_date')
                                                    ->label('Date')
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
                                                    ->default('bank_transfer')
                                                    ->required(),

                                                TextInput::make('reference_number')
                                                    ->label('Reference / Cheque / Trx #')
                                                    ->placeholder('Cheque no. or Trx ID'),

                                                FileUpload::make('receipt_image')
                                                    ->label('Payment Voucher / Receipt Photo')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('purchase-payments'),

                                                TextInput::make('notes')
                                                    ->label('Payment Remarks')
                                                    ->placeholder('e.g. Advance payment / Final settlement'),
                                            ]),
                                    ])
                                    ->addActionLabel('Record Payment')
                                    ->defaultItems(0),
                            ]),

                        Tab::make('Photos & Bills')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                Section::make('Vendor Bill & Invoice Images')
                                    ->description('Upload photographs or scanned copies of the physical vendor bill / tax invoice.')
                                    ->schema([
                                        FileUpload::make('bill_images')
                                            ->label('Bill Photos')
                                            ->multiple()
                                            ->image()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('purchase-bills')
                                            ->panelLayout('grid')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Received Products & Materials Photos')
                                    ->description('Photographs of the actual materials/products delivered (fabrics, buttons, rolls, packaging).')
                                    ->schema([
                                        FileUpload::make('product_images')
                                            ->label('Product / Delivery Photos')
                                            ->multiple()
                                            ->image()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('purchase-products')
                                            ->panelLayout('grid')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Notes & Summary')
                            ->icon(Heroicon::OutlinedClipboardDocumentList)
                            ->schema([
                                Section::make('Summary & Remarks')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                Placeholder::make('summary_total')
                                                    ->label('Total Order Amount')
                                                    ->content(fn ($record): string => 'NPR '.number_format((float) ($record?->total_amount ?? 0), 2)),

                                                Placeholder::make('summary_paid')
                                                    ->label('Total Paid')
                                                    ->content(fn ($record): string => 'NPR '.number_format((float) ($record?->paid_amount ?? 0), 2)),

                                                Placeholder::make('summary_due')
                                                    ->label('Outstanding Due')
                                                    ->content(fn ($record): string => 'NPR '.number_format((float) ($record?->due_amount ?? 0), 2)),
                                            ]),

                                        Textarea::make('notes')
                                            ->label('Internal Notes / Remarks')
                                            ->rows(3)
                                            ->placeholder('Any special terms, transport details, vehicle number, or remarks...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
