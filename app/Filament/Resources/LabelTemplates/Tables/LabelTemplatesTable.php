<?php

namespace App\Filament\Resources\LabelTemplates\Tables;

use App\Models\LabelTemplate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                    ->state(fn (LabelTemplate $record) => is_array($record->elements) ? (isset($record->elements['objects']) ? count($record->elements['objects']) : count($record->elements)) . ' items' : '0 items')
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
                Action::make('quick_print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->slideOver()
                    ->modalWidth('4xl')
                    ->modalHeading(fn (LabelTemplate $record) => "Print Label: {$record->name}")
                    ->modalDescription(fn (LabelTemplate $record) => "{$record->width_mm}mm × {$record->height_mm}mm (Gap {$record->gap_mm}mm) — Real-time 1-bit thermal preview")
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalContent(fn (LabelTemplate $record) => view('filament.label-templates.quick-print-modal', [
                        'template' => $record,
                        'printers' => \App\Models\Printer::where('is_active', true)->get(),
                    ])),

                Action::make('open_in_studio')
                    ->label('Studio')
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (LabelTemplate $record) => url("/label-studio/{$record->id}")),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
