<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Vendor Information')
                    ->tabs([
                        Tab::make('General Details')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema([
                                Section::make('Vendor Identity & Contact')
                                    ->description('Basic information, registration, and communication details.')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Vendor / Company Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('e.g. Denim Fabric Mills Pvt. Ltd.'),

                                                TextInput::make('email')
                                                    ->label('Email Address')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->placeholder('vendor@example.com'),

                                                TextInput::make('pan_vat_number')
                                                    ->label('PAN / VAT Number')
                                                    ->maxLength(50)
                                                    ->placeholder('e.g. 601234567'),

                                                TextInput::make('website')
                                                    ->label('Website URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.vendor.com'),

                                                Textarea::make('address')
                                                    ->label('Physical / Factory Address')
                                                    ->rows(2)
                                                    ->columnSpanFull()
                                                    ->placeholder('Street, City, Province / District'),
                                            ]),
                                    ]),

                                Section::make('Status & Notes')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label('Active Vendor')
                                                    ->helperText('Active vendors are selectable for purchase orders and items.')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('danger'),

                                                Textarea::make('notes')
                                                    ->label('Internal Notes / Terms')
                                                    ->rows(2)
                                                    ->placeholder('Payment terms, credit period, delivery lead times, etc.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Contact Persons')
                            ->icon(Heroicon::OutlinedUsers)
                            ->badge(fn ($record): ?string => $record && is_array($record->contacts) ? (string) count($record->contacts) : null)
                            ->schema([
                                Repeater::make('contacts')
                                    ->label('Authorized Contacts')
                                    ->itemLabel(fn (array $state): ?string => $state['contact_name'] ?? 'New Contact')
                                    ->collapsible()
                                    ->cloneable()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('contact_name')
                                                    ->label('Contact Name')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('designation')
                                                    ->label('Designation / Department')
                                                    ->placeholder('e.g. Sales Manager, Merchandiser')
                                                    ->maxLength(255),

                                                TextInput::make('email')
                                                    ->label('Personal / Direct Email')
                                                    ->email()
                                                    ->maxLength(255),
                                            ]),

                                        Repeater::make('phone_numbers')
                                            ->label('Phone Numbers')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('type')
                                                            ->label('Type')
                                                            ->options([
                                                                'mobile' => 'Mobile',
                                                                'whatsapp' => 'WhatsApp',
                                                                'office' => 'Office / Landline',
                                                                'factory' => 'Factory Line',
                                                                'other' => 'Other',
                                                            ])
                                                            ->default('mobile')
                                                            ->required(),

                                                        TextInput::make('number')
                                                            ->label('Phone Number')
                                                            ->tel()
                                                            ->required()
                                                            ->placeholder('+977 98XXXXXXXX'),
                                                    ]),
                                            ])
                                            ->columns(1)
                                            ->addActionLabel('Add Phone Number')
                                            ->defaultItems(1),
                                    ])
                                    ->addActionLabel('Add Contact Person')
                                    ->defaultItems(1),
                            ]),

                        Tab::make('Banking Details')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->badge(fn ($record): ?string => $record && is_array($record->bank_accounts) ? (string) count($record->bank_accounts) : null)
                            ->schema([
                                Repeater::make('bank_accounts')
                                    ->label('Vendor Bank Accounts')
                                    ->itemLabel(fn (array $state): ?string => filled($state['bank_name'] ?? null) ? "{$state['bank_name']} - {$state['account_number']}" : 'Bank Account')
                                    ->collapsible()
                                    ->cloneable()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('bank_name')
                                                    ->label('Bank Name')
                                                    ->required()
                                                    ->placeholder('e.g. Nabil Bank, Everest Bank')
                                                    ->maxLength(255),

                                                TextInput::make('account_name')
                                                    ->label('Account Name / Beneficiary')
                                                    ->required()
                                                    ->placeholder('Exact name on account')
                                                    ->maxLength(255),

                                                TextInput::make('account_number')
                                                    ->label('Account Number')
                                                    ->required()
                                                    ->maxLength(100),

                                                TextInput::make('branch')
                                                    ->label('Branch Name')
                                                    ->placeholder('e.g. New Road, Birgunj')
                                                    ->maxLength(255),

                                                TextInput::make('routing_code')
                                                    ->label('Routing / Swift Code')
                                                    ->placeholder('Optional code')
                                                    ->maxLength(100),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Bank Account')
                                    ->defaultItems(1),
                            ]),

                        Tab::make('Payment QRs')
                            ->icon(Heroicon::OutlinedQrCode)
                            ->badge(fn ($record): ?string => $record && is_array($record->qrs) ? (string) count($record->qrs) : null)
                            ->schema([
                                Repeater::make('qrs')
                                    ->label('Digital Payment QRs')
                                    ->itemLabel(fn (array $state): ?string => filled($state['qr_type'] ?? null) ? strtoupper($state['qr_type']).' QR' : 'Payment QR')
                                    ->collapsible()
                                    ->grid(2)
                                    ->schema([
                                        Select::make('qr_type')
                                            ->label('QR Type / Wallet')
                                            ->options([
                                                'esewa' => 'eSewa',
                                                'khalti' => 'Khalti',
                                                'fonepay' => 'Fonepay / Bank Smart QR',
                                                'connectips' => 'ConnectIPS',
                                                'bank' => 'Direct Bank QR',
                                                'other' => 'Other Wallet / QR',
                                            ])
                                            ->required(),

                                        TextInput::make('account_title')
                                            ->label('Account Title / Registered Name')
                                            ->placeholder('e.g. Merchant Name or Mobile Number')
                                            ->maxLength(255),

                                        FileUpload::make('qr_image')
                                            ->label('QR Code Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('vendor-qrs')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ])
                                    ->addActionLabel('Add Payment QR')
                                    ->defaultItems(0),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
