<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors\Schemas;

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

class DistributorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Distributor Information')
                    ->tabs([
                        Tab::make('Company Details')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                Section::make('Distributor Identity & Contact')
                                    ->description('Official corporate information and business location.')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Company / Distributor Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('e.g. Himalayan Apparel Distributors Pvt. Ltd.'),

                                                TextInput::make('code')
                                                    ->label('Distributor Code')
                                                    ->maxLength(50)
                                                    ->placeholder('e.g. DIST-001'),

                                                TextInput::make('pan_vat_number')
                                                    ->label('PAN / VAT Number')
                                                    ->maxLength(50)
                                                    ->placeholder('e.g. 601234567'),

                                                TextInput::make('email')
                                                    ->label('Official Email Address')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->placeholder('orders@distributor.com'),

                                                TextInput::make('phone')
                                                    ->label('Official Phone / Landline')
                                                    ->tel()
                                                    ->maxLength(50)
                                                    ->placeholder('+977 1 4XXXXXX'),

                                                TextInput::make('website')
                                                    ->label('Website URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.distributor.com'),

                                                Textarea::make('address')
                                                    ->label('Physical / Warehouse Address')
                                                    ->rows(2)
                                                    ->columnSpanFull()
                                                    ->placeholder('Street, City, District / Province'),
                                            ]),
                                    ]),

                                Section::make('Account Status & Notes')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label('Active Distributor')
                                                    ->helperText('Active distributors appear in sales records dropdown.')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('danger'),

                                                Textarea::make('notes')
                                                    ->label('Internal Notes / Credit Terms')
                                                    ->rows(2)
                                                    ->placeholder('Credit terms (e.g. 30 days credit, 10% advance deposit).'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Attached Contacts')
                            ->icon(Heroicon::OutlinedUsers)
                            ->badge(fn ($record): ?string => $record && is_array($record->contacts) ? (string) count($record->contacts) : null)
                            ->schema([
                                Repeater::make('contacts')
                                    ->label('Authorized Contact Persons')
                                    ->itemLabel(fn (array $state): ?string => $state['contact_name'] ?? 'New Contact')
                                    ->collapsible()
                                    ->cloneable()
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextInput::make('contact_name')
                                                    ->label('Contact Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('e.g. Ramesh Shrestha'),

                                                TextInput::make('designation')
                                                    ->label('Designation / Role')
                                                    ->maxLength(255)
                                                    ->placeholder('e.g. Sales Director, Warehouse Manager'),

                                                TextInput::make('email')
                                                    ->label('Direct Email')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->placeholder('ramesh@distributor.com'),
                                            ]),

                                        Repeater::make('phone_numbers')
                                            ->label('Phone Numbers')
                                            ->schema([
                                                Grid::make(['default' => 1, 'sm' => 2])
                                                    ->schema([
                                                        Select::make('type')
                                                            ->label('Type')
                                                            ->options([
                                                                'mobile' => 'Mobile',
                                                                'whatsapp' => 'WhatsApp',
                                                                'office' => 'Office Line',
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
                                    ->defaultItems(0),
                            ]),

                        Tab::make('Banking Details')
                            ->icon(Heroicon::OutlinedBuildingLibrary)
                            ->badge(fn ($record): ?string => $record && is_array($record->bank_accounts) ? (string) count($record->bank_accounts) : null)
                            ->schema([
                                Repeater::make('bank_accounts')
                                    ->label('Distributor Bank Accounts (Optional)')
                                    ->itemLabel(fn (array $state): ?string => $state['bank_name'] ?? 'Bank Account')
                                    ->collapsible()
                                    ->cloneable()
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('bank_name')
                                                    ->label('Bank Name')
                                                    ->required()
                                                    ->placeholder('e.g. Nabil Bank, Global IME Bank'),

                                                TextInput::make('account_name')
                                                    ->label('Account Holder Name')
                                                    ->required()
                                                    ->placeholder('e.g. Himalayan Apparel Distributors Pvt. Ltd.'),

                                                TextInput::make('account_number')
                                                    ->label('Account Number')
                                                    ->required()
                                                    ->placeholder('e.g. 01201017500000'),

                                                TextInput::make('branch')
                                                    ->label('Branch Name')
                                                    ->placeholder('e.g. New Road Branch, Kathmandu'),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Bank Account')
                                    ->defaultItems(0),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
