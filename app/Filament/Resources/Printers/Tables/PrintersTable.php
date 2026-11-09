<?php

namespace App\Filament\Resources\Printers\Tables;

use App\Models\Printer;
use App\Services\PrintService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrintersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Printer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (Printer $record) {
                        if ($record->type === 'cups') {
                            return 'CUPS Queue: ' . ($record->cups_queue_name ?: 'None');
                        }
                        return 'Network: ' . $record->ip_address . ':' . $record->port;
                    }),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->color(fn ($state) => $state === 'cups' ? 'info' : 'primary'),

                TextColumn::make('protocol')
                    ->label('Protocol')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Connection Status')
                    ->state(fn (Printer $record) => $record->getStatus()['message'])
                    ->badge()
                    ->color(fn (Printer $record) => $record->getStatus()['color']),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('webusb_test_print')
                    ->label('USB Test')
                    ->visible(fn (Printer $record) => $record->type === 'webusb')
                    ->view('filament.tables.actions.webusb-test-action'),

                Action::make('test_print')
                    ->label('Test Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (Printer $record) => $record->type !== 'webusb')
                    ->requiresConfirmation()
                    ->modalHeading('Send Test Print')
                    ->modalDescription('This will send a small test label to verify communication with this printer.')
                    ->action(function (Printer $record) {
                        $testTspl = "SIZE 50 mm, 30 mm\nGAP 2 mm, 0 mm\nDIRECTION 1\nCLS\nTEXT 40,40,\"3\",0,1,1,\"7 SERIES KAMKAJ\"\nTEXT 40,80,\"2\",0,1,1,\"Printer: {$record->name}\"\nTEXT 40,120,\"2\",0,1,1,\"Status: TEST OK!\"\nBAR 40,160,280,2\nPRINT 1\n";
                        $result = PrintService::sendRawPrint($record, $testTspl);
                        if ($result['success']) {
                            Notification::make()
                                ->title('Test print sent!')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Print failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('4xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
