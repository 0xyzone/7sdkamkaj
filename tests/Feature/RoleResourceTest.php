<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_render_shield_roles_page(): void
    {
        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => Utils::getSuperAdminName(),
            'guard_name' => 'web',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $admin->is_active = true;
        $admin->save();
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->get('/shield/roles');

        $response->assertSuccessful();
    }

    public function test_can_render_list_roles_livewire_component(): void
    {
        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => Utils::getSuperAdminName(),
            'guard_name' => 'web',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $admin->is_active = true;
        $admin->save();
        $admin->assignRole($role);

        Livewire::actingAs($admin)
            ->test(ListRoles::class)
            ->assertSuccessful()
            ->assertActionExists('create');
    }
}
