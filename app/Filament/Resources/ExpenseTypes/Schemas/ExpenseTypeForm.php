<?php

namespace App\Filament\Resources\ExpenseTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Category Information')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Category Name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g. Factory Rent, Staff Salary, Groceries')
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Short Code')
                                    ->placeholder('e.g. RENT, SAL, GROC')
                                    ->maxLength(50),

                                Toggle::make('is_active')
                                    ->label('Active Status')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText('Active categories appear in expense entry forms.'),
                            ]),

                        Textarea::make('description')
                            ->label('Description & Scope')
                            ->placeholder('Describe what factory expenditures belong in this category...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
