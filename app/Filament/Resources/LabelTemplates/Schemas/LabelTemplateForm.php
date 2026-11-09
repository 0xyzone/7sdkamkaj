<?php

namespace App\Filament\Resources\LabelTemplates\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LabelTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Label Configuration & Paper Size')
                    ->description('Select standard thermal label sizes or configure a custom sticker size (Max nozzle: 108mm width).')
                    ->collapsible()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Template Name')
                                    ->placeholder('e.g. Standard Garment Tag 50x30')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('standard_preset')
                                    ->label('Standard Size Preset')
                                    ->dehydrated(false)
                                    ->options([
                                        '50x30' => '50mm × 30mm (Standard Garment Tag)',
                                        '50x70' => '50mm × 70mm (Garment Wash Care & Price)',
                                        '40x30' => '40mm × 30mm (Compact Price Sticker)',
                                        '60x40' => '60mm × 40mm (Medium Box / Garment Tag)',
                                        '80x50' => '80mm × 50mm (Large Brand Card)',
                                        '100x150' => '100mm × 150mm (Shipping / Carton Label)',
                                        'custom' => 'Custom Dimension (Manual Entry)',
                                    ])
                                    ->default('50x30')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state === '50x30') {
                                            $set('width_mm', 50.00);
                                            $set('height_mm', 30.00);
                                        } elseif ($state === '50x70') {
                                            $set('width_mm', 50.00);
                                            $set('height_mm', 70.00);
                                        } elseif ($state === '40x30') {
                                            $set('width_mm', 40.00);
                                            $set('height_mm', 30.00);
                                        } elseif ($state === '60x40') {
                                            $set('width_mm', 60.00);
                                            $set('height_mm', 40.00);
                                        } elseif ($state === '80x50') {
                                            $set('width_mm', 80.00);
                                            $set('height_mm', 50.00);
                                        } elseif ($state === '100x150') {
                                            $set('width_mm', 100.00);
                                            $set('height_mm', 150.00);
                                        }
                                    }),

                                Select::make('category')
                                    ->label('Category')
                                    ->options([
                                        'garment' => 'Garment Tag / Size / Price',
                                        'wash_care' => 'Wash & Care Instructions',
                                        'barcode' => 'Barcode / Inventory Label',
                                        'shipping' => 'Shipping / Packaging',
                                    ])
                                    ->default('garment')
                                    ->required(),
                            ]),

                        Grid::make(['default' => 2, 'sm' => 4])
                            ->schema([
                                TextInput::make('width_mm')
                                    ->label('Width (mm)')
                                    ->helperText('Deli E886BW max print width: 108mm')
                                    ->numeric()
                                    ->minValue(20)
                                    ->maxValue(108)
                                    ->default(50.00)
                                    ->required()
                                    ->live(),

                                TextInput::make('height_mm')
                                    ->label('Height (mm)')
                                    ->numeric()
                                    ->minValue(15)
                                    ->maxValue(300)
                                    ->default(30.00)
                                    ->required()
                                    ->live(),

                                TextInput::make('gap_mm')
                                    ->label('Label Gap (mm)')
                                    ->numeric()
                                    ->default(2.00)
                                    ->required(),

                                Select::make('direction')
                                    ->label('Print Direction')
                                    ->options([
                                        1 => 'Normal (Top to Bottom)',
                                        0 => 'Inverted (180° Flip)',
                                    ])
                                    ->default(1)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Visual Label Designer')
                    ->description('Design your label interactively with Text, Barcodes, QR codes, Shapes, and freehand drawing tools.')
                    ->schema([
                        Field::make('elements')
                            ->label('Canvas Elements')
                            ->view('filament.forms.components.label-canvas-designer')
                            ->columnSpanFull(),
                    ]),

                Section::make('Additional Settings')
                    ->collapsed()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Template')
                            ->default(true),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

