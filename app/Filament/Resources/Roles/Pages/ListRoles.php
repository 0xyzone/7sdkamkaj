<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalWidth('7xl')
                ->using(function (array $data, string $model): Model {
                    $roleData = Arr::only($data, ['name', 'guard_name']);

                    if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
                        $roleData[Utils::getTenantModelForeignKey()] = $data[Utils::getTenantModelForeignKey()];
                    }

                    return $model::create($roleData);
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
        ];
    }
}
