<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles;

use Agroezinger\FilamentShieldEnhanced\Forms\EnhancedComponentPermissionsForm;
use Agroezinger\FilamentShieldEnhanced\Forms\EnhancedPagePermissionsForm;
use Agroezinger\FilamentShieldEnhanced\Forms\EnhancedResourcePermissionsForm;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Override;
use UnitEnum;

class RoleResource extends Resource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use HasShieldFormComponents;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'User Management';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-shield-check';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule): Unique => Utils::isTenancyEnabled() ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id) : $rule
                                    )
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),

                                static::getSelectAllFormComponent(),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                static::getShieldFormComponents(),
            ]);
    }

    public static function getShieldFormComponents(): Component
    {
        $resourceComponents = EnhancedResourcePermissionsForm::make();
        $resourceCount = array_sum(array_map('count', EnhancedResourcePermissionsForm::getResourcePermissionFields()));

        $pageComponents = EnhancedPagePermissionsForm::make();
        $pageCount = array_sum(array_map('count', EnhancedPagePermissionsForm::getPagePermissionFields()));

        $componentComponents = EnhancedComponentPermissionsForm::make();
        $componentCount = array_sum(array_map('count', EnhancedComponentPermissionsForm::getComponentPermissionFields()));

        $tabs = [];

        if (! empty($resourceComponents)) {
            $tabs[] = Tab::make('resources')
                ->label('Resources')
                ->badge($resourceCount ?: null)
                ->schema($resourceComponents);
        }

        if (! empty($pageComponents)) {
            $tabs[] = Tab::make('pages')
                ->label('Pages')
                ->badge($pageCount ?: null)
                ->schema($pageComponents);
        }

        if (! empty($componentComponents)) {
            $tabs[] = Tab::make('components')
                ->label('Components')
                ->badge($componentCount ?: null)
                ->schema($componentComponents);
        }

        $tabs[] = static::getTabFormComponentForWidget();
        $tabs[] = static::getTabFormComponentForCustomPermissions();

        return Tabs::make('Permissions')
            ->contained()
            ->tabs(array_filter($tabs))
            ->columnSpanFull();
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),
                TextColumn::make('team.name')
                    ->default('Global')
                    ->badge()
                    ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                    ->label(__('filament-shield::filament-shield.column.team'))
                    ->searchable()
                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->stackedOnMobile()
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        $existing = $record->permissions->pluck('name')->all();

                        foreach (EnhancedPagePermissionsForm::getPagePermissionFields() as $fieldName => $keys) {
                            $data[$fieldName] = array_values(array_intersect($keys, $existing));
                        }

                        foreach (EnhancedResourcePermissionsForm::getResourcePermissionFields() as $fieldName => $keys) {
                            $data[$fieldName] = array_values(array_intersect($keys, $existing));
                        }

                        foreach (EnhancedComponentPermissionsForm::getComponentPermissionFields() as $fieldName => $keys) {
                            $data[$fieldName] = array_values(array_intersect($keys, $existing));
                        }

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $roleData = Arr::only($data, ['name', 'guard_name']);

                        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
                            $roleData[Utils::getTenantModelForeignKey()] = $data[Utils::getTenantModelForeignKey()];
                        }

                        $record->update($roleData);

                        return $record;
                    })
                    ->after(function (Model $record, array $data): void {
                        $permissions = collect($data)
                            ->filter(fn (mixed $permission, string $key): bool => ! in_array($key, ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()], true))
                            ->values()
                            ->flatten()
                            ->unique();

                        $permissionModels = collect();
                        $permissions->each(function (string $permission) use ($permissionModels, $data): void {
                            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                                'name' => $permission,
                                'guard_name' => $data['guard_name'] ?? Utils::getFilamentAuthGuard(),
                            ]));
                        });

                        $record->syncPermissions($permissionModels);
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
        ];
    }

    #[Override]
    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return Utils::getResourceSlug();
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster();
    }

    public static function getEssentialsPlugin(): ?FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }
}
