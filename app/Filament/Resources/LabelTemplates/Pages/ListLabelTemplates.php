<?php

namespace App\Filament\Resources\LabelTemplates\Pages;

use App\Filament\Resources\LabelTemplates\LabelTemplateResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListLabelTemplates extends ListRecords
{
    protected static string $resource = LabelTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_in_studio')
                ->label('New in Label Studio')
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(url('/label-studio?new=1')),
        ];
    }
}
