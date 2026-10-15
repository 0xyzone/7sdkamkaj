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

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->mutateFormDataBeforeCreate(function (array $data): array {
                    return Arr::only($data, ['name', 'guard_name']);
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
