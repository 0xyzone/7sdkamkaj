<?php

namespace App\Filament\Resources\LabelTemplates;

use App\Filament\Resources\LabelTemplates\Pages\ListLabelTemplates;
use App\Filament\Resources\LabelTemplates\Schemas\LabelTemplateForm;
use App\Filament\Resources\LabelTemplates\Tables\LabelTemplatesTable;
use App\Models\LabelTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LabelTemplateResource extends Resource
{
    protected static ?string $model = LabelTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Printing & Labels';

    protected static ?string $navigationLabel = 'Label Templates';

    protected static ?string $modelLabel = 'Label Template';

    protected static ?string $pluralModelLabel = 'Label Templates';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return LabelTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabelTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabelTemplates::route('/'),
        ];
    }
}

