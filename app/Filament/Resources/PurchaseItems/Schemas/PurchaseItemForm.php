<?php

namespace App\Filament\Resources\PurchaseItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PurchaseItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Item Details')
                    ->description('Specify material identification, classification, and preferred vendor.')
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Item / Material Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Denim Fabric 12oz, Brass Zipper 5#, Copper Rivets'),

                                Select::make('category')
                                    ->label('Category / Material Type')
                                    ->options([
                                        'Fabric / Denim' => 'Fabric / Denim',
                                        'Trims & Hardware' => 'Trims & Hardware (Zippers, Buttons, Rivets)',
                                        'Thread & Yarn' => 'Thread & Yarn',
                                        'Labels & Tags' => 'Labels & Tags',
                                        'Pocketing & Lining' => 'Pocketing & Lining',
                                        'Accessories & Elastic' => 'Accessories & Elastic',
                                        'Packaging Materials' => 'Packaging Materials',
                                        'Chemicals & Washes' => 'Chemicals & Washes',
                                        'Other Raw Material' => 'Other Raw Material',
                                    ])
                                    ->searchable()
                                    ->placeholder('Select Category'),

                                Select::make('unit_of_measurement')
                                    ->label('Unit of Measurement (UOM)')
                                    ->options([
                                        'Meters' => 'Meters (m)',
                                        'Yards' => 'Yards (yd)',
                                        'Pieces' => 'Pieces (pcs)',
                                        'Kilograms' => 'Kilograms (kg)',
                                        'Cones' => 'Cones',
                                        'Rolls' => 'Rolls',
                                        'Gross' => 'Gross (144 pcs)',
                                        'Dozen' => 'Dozen (12 pcs)',
                                        'Sets' => 'Sets',
                                    ])
                                    ->searchable()
                                    ->placeholder('Select Unit'),

                                TextInput::make('cost_price')
                                    ->label('Default Cost / Estimate (NPR)')
                                    ->numeric()
                                    ->prefix('NPR')
                                    ->placeholder('0.00'),

                                Select::make('vendor_id')
                                    ->label('Preferred Vendor')
                                    ->relationship('vendor', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select Vendor (Optional)'),

                                Toggle::make('is_active')
                                    ->label('Active for Purchasing')
                                    ->helperText('Active items appear in purchase orders and inventory logs.')
                                    ->default(true)
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Textarea::make('description')
                            ->label('Specifications & Technical Notes')
                            ->rows(3)
                            ->placeholder('e.g. 100% Cotton, 3/1 Right Hand Twill, 12 oz/sq.yd, Indigo Dyed, Shrinkage 3%, Width 58 inch')
                            ->columnSpanFull(),
                    ]),

                Section::make('Item Images & Reference Gallery')
                    ->description('Upload swatches, photos, and spec sheets for this item.')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        FileUpload::make('images')
                            ->label('Item Images')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->disk('public')
                            ->directory('purchase-items')
                            ->panelLayout('grid')
                            ->imageEditor()
                            ->maxFiles(10)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
