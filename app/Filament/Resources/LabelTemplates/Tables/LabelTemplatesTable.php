<?php

namespace App\Filament\Resources\LabelTemplates\Tables;

use App\Models\LabelTemplate;
use App\Models\Printer;
use App\Services\PrintService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LabelTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Template Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (LabelTemplate $record) => "{$record->width_mm}mm × {$record->height_mm}mm (Gap {$record->gap_mm}mm)"),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->color('info'),

                TextColumn::make('elements_count')
                    ->label('Design Elements')
                    ->state(fn (LabelTemplate $record) => count($record->elements ?? []) . ' items')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Send to Printer')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading(fn (LabelTemplate $record) => "Print '{$record->name}' ({$record->width_mm}mm × {$record->height_mm}mm)")
                    ->form([
                        Select::make('printer_id')
                            ->label('Select Destination Printer')
                            ->options(fn () => Printer::where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => Printer::where('is_default', true)->first()?->id ?? Printer::first()?->id)
                            ->required()
                            ->helperText('Choose which physical printer will receive this label print command.'),

                        TextInput::make('copies')
                            ->label('Number of Copies')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(500)
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('brand')
                                    ->label('Brand Name')
                                    ->default('SEVEN SERIES'),

                                TextInput::make('item_name')
                                    ->label('Item / Article Name')
                                    ->default('Authentic Denim Jeans'),

                                TextInput::make('size')
                                    ->label('Garment Size')
                                    ->default('32'),

                                TextInput::make('price')
                                    ->label('MRP / Price')
                                    ->default('Rs. 2,999'),

                                TextInput::make('barcode')
                                    ->label('Barcode Number')
                                    ->default('7S-DENIM-32-001')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (LabelTemplate $record, array $data) {
                        $printer = Printer::find($data['printer_id']);
                        if (! $printer) {
                            Notification::make()
                                ->title('Error')
                                ->body('Selected printer was not found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $sampleData = [
                            'brand' => $data['brand'] ?? 'SEVEN SERIES',
                            'item_name' => $data['item_name'] ?? 'Authentic Denim Jeans',
                            'size' => $data['size'] ?? '32',
                            'price' => $data['price'] ?? 'Rs. 2,999',
                            'barcode' => $data['barcode'] ?? '7S-DENIM-32-001',
                        ];

                        $tspl = $record->compileTspl($sampleData, (int) ($data['copies'] ?? 1));

                        $result = PrintService::sendRawPrint($printer, $tspl);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Print Command Dispatched!')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Print Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

