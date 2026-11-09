<?php

namespace App\Filament\Resources\Printers\Schemas;

use App\Services\PrintService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PrinterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Printer Name')
                    ->placeholder('e.g. Deli E886BW - Factory Floor')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Connection Type')
                    ->options([
                        'webusb' => 'Direct USB (Client Mac/Windows via WebUSB in Brave/Chrome)',
                        'bluetooth' => 'Wireless Bluetooth (Direct Client Spool)',
                        'cups' => 'Local CUPS Printer (Server local spooler)',
                        'network' => 'Network (Direct Raw TCP / IP Port 9100)',
                    ])
                    ->default('webusb')
                    ->required()
                    ->live(),

                Select::make('cups_queue_name')
                    ->label('CUPS Printer Queue')
                    ->helperText('Detected CUPS printers or manual queue name (e.g. Deli_886BW).')
                    ->options(fn () => PrintService::getAvailableCupsPrinters())
                    ->searchable()
                    ->createOptionUsing(fn (string $data) => trim($data))
                    ->visible(fn ($get) => $get('type') === 'cups')
                    ->required(fn ($get) => $get('type') === 'cups'),

                TextInput::make('ip_address')
                    ->label('Printer IP Address')
                    ->placeholder('192.168.1.150')
                    ->helperText('Enter the local Wi-Fi or LAN IP address of your printer.')
                    ->visible(fn ($get) => $get('type') === 'network')
                    ->required(fn ($get) => $get('type') === 'network'),

                TextInput::make('port')
                    ->label('Port')
                    ->numeric()
                    ->default(9100)
                    ->helperText('Standard raw socket printing port is 9100.')
                    ->visible(fn ($get) => $get('type') === 'network')
                    ->required(fn ($get) => $get('type') === 'network'),

                Select::make('protocol')
                    ->label('Printer Command Language')
                    ->options([
                        'tspl' => 'TSPL (Deli, TSC, standard label printers)',
                        'zpl' => 'ZPL (Zebra compatible)',
                        'escpos' => 'ESC/POS (Standard receipt printers)',
                    ])
                    ->default('tspl')
                    ->required(),

                Toggle::make('is_default')
                    ->label('Default Printer')
                    ->helperText('Use this printer as the default destination for printing')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('Active / Enabled')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
