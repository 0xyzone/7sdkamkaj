<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserRedirectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_is_redirected_to_account_deactivated_page(): void
    {
        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => Utils::getSuperAdminName(),
            'guard_name' => 'web',
        ]);

        $inactiveUser = User::create([
            'name' => 'Inactive Worker',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);
        $inactiveUser->assignRole($role);

        $response = $this->actingAs($inactiveUser)->get('/');

        $response->assertRedirect('/account-deactivated');
    }

    public function test_account_deactivated_page_renders_message_and_logout_button(): void
    {
        $inactiveUser = User::create([
            'name' => 'Deactivated User',
            'email' => 'deactivated@test.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get('/account-deactivated');

        $response->assertSuccessful();
        $response->assertSee('Account Deactivated');
        $response->assertSee('Your account has been deactivated. Please contact the system administrator or the factory owner to reactivate your account.');
        $response->assertSee('Log Out');
        $response->assertSee(route('account.deactivated.logout'));
    }

    public function test_inactive_user_can_logout_from_deactivated_page(): void
    {
        $inactiveUser = User::create([
            'name' => 'Deactivated User',
            'email' => 'deactivated2@test.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->post('/account-deactivated/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_active_user_visiting_deactivated_page_is_redirected_to_panel(): void
    {
        $activeUser = User::create([
            'name' => 'Active User',
            'email' => 'active@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($activeUser)->get('/account-deactivated');

        $response->assertRedirect('/');
    }

    public function test_guest_visiting_deactivated_page_is_redirected_to_login(): void
    {
        $response = $this->get('/account-deactivated');

        $response->assertRedirect('/login');
    }
}
