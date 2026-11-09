<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LabelTemplate;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_render_printers_and_label_templates_pages(): void
    {
        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => Utils::getSuperAdminName(),
            'guard_name' => 'web',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $admin->assignRole($role);

        $responsePrinters = $this->actingAs($admin)->get('/printers');
        $responsePrinters->assertSuccessful();

        $responseLabels = $this->actingAs($admin)->get('/label-templates');
        $responseLabels->assertSuccessful();
    }

    public function test_label_template_compiles_tspl(): void
    {
        $template = LabelTemplate::create([
            'name' => 'Test Garment 50x30',
            'category' => 'garment',
            'width_mm' => 50,
            'height_mm' => 30,
            'gap_mm' => 2,
            'direction' => 1,
            'elements' => [
                ['type' => 'text', 'x' => 20, 'y' => 20, 'content' => 'SEVEN SERIES - {{size}}'],
                ['type' => 'barcode', 'x' => 20, 'y' => 60, 'content' => '{{barcode}}'],
            ],
        ]);

        $tspl = $template->compileTspl([
            'size' => '32',
            'barcode' => '7S-DENIM-001',
        ], 2);

        $this->assertStringContainsString('SIZE 50', $tspl);
        $this->assertStringContainsString('GAP 2', $tspl);
        $this->assertStringContainsString('SEVEN SERIES - 32', $tspl);
        $this->assertStringContainsString('BARCODE 20,60,"128"', $tspl);
        $this->assertStringContainsString('PRINT 2', $tspl);
    }
}
