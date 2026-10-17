<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Distributors\Pages\ListDistributors;
use App\Filament\Resources\SaleRecords\Pages\ListSaleRecords;
use App\Models\Distributor;
use App\Models\SaleRecord;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => Utils::getSuperAdminName(),
            'guard_name' => 'web',
        ]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $this->admin->is_active = true;
        $this->admin->save();
        $this->admin->assignRole($role);
    }

    public function test_distributor_resource_page_loads_and_has_create_action(): void
    {
        $response = $this->actingAs($this->admin)->get('/distributors');
        $response->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListDistributors::class)
            ->assertSuccessful()
            ->assertActionExists('create');
    }

    public function test_sale_record_resource_page_loads_and_has_create_action(): void
    {
        $response = $this->actingAs($this->admin)->get('/sale-records');
        $response->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListSaleRecords::class)
            ->assertSuccessful()
            ->assertActionExists('create');
    }

    public function test_sale_record_auto_numbering_and_payment_status_calculation(): void
    {
        $distributor = Distributor::create([
            'name' => 'Kathmandu Denim Hub',
            'phone' => '+977 1 4234567',
            'pan_vat_number' => '600123456',
            'is_active' => true,
        ]);

        $sale = SaleRecord::create([
            'distributor_id' => $distributor->id,
            'bill_number' => 'BILL-2026-001',
            'sale_date' => now()->toDateString(),
            'total_amount' => 50000.00,
            'status' => 'completed',
        ]);

        $this->assertNotNull($sale->sale_number);
        $this->assertStringStartsWith('SALE-', $sale->sale_number);
        $this->assertEquals(0, (float) $sale->paid_amount);
        $this->assertEquals(50000, (float) $sale->due_amount);
        $this->assertEquals('unpaid', $sale->payment_status);

        // Add partial payment
        $sale->payments = [
            [
                'payment_date' => now()->toDateString(),
                'amount' => 20000.00,
                'payment_method' => 'bank_transfer',
            ],
        ];
        $sale->save();

        $sale->refresh();
        $this->assertEquals(20000, (float) $sale->paid_amount);
        $this->assertEquals(30000, (float) $sale->due_amount);
        $this->assertEquals('partial', $sale->payment_status);

        // Add remaining payment
        $sale->payments = [
            [
                'payment_date' => now()->toDateString(),
                'amount' => 20000.00,
                'payment_method' => 'bank_transfer',
            ],
            [
                'payment_date' => now()->toDateString(),
                'amount' => 30000.00,
                'payment_method' => 'cash',
            ],
        ];
        $sale->save();

        $sale->refresh();
        $this->assertEquals(50000, (float) $sale->paid_amount);
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
    }
}
