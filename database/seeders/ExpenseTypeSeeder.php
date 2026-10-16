<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Salary & Wages',
                'code' => 'SALARY',
                'description' => 'Worker daily wages, tailor piece-rates, master cutting charges, and staff monthly payroll.',
                'is_active' => true,
            ],
            [
                'name' => 'Factory & Godown Rent',
                'code' => 'RENT',
                'description' => 'Monthly rental for factory floor, storage godowns, and office space.',
                'is_active' => true,
            ],
            [
                'name' => 'Groceries & Kitchen / Canteen',
                'code' => 'GROCERIES',
                'description' => 'Food grains, vegetables, milk, tea, snacks, cooking gas, and staff mess supplies.',
                'is_active' => true,
            ],
            [
                'name' => 'Office Resources & Stationery',
                'code' => 'OFFICE',
                'description' => 'Printing paper, printer cartridges, pens, staplers, registers, and admin supplies.',
                'is_active' => true,
            ],
            [
                'name' => 'Machinery & Equipment Maintenance',
                'code' => 'MAINT',
                'description' => 'Sewing machine servicing, spare needles, lubricating oil, belt replacements, and iron repair.',
                'is_active' => true,
            ],
            [
                'name' => 'Utilities (Electricity, Water, Fuel)',
                'code' => 'UTILITIES',
                'description' => 'NEA electricity bills, water tankers, generator diesel, and LPG cylinders.',
                'is_active' => true,
            ],
            [
                'name' => 'Transportation & Logistics',
                'code' => 'LOGISTICS',
                'description' => 'Freight transport, parcel couriers, loading/unloading labor, and local delivery van fuel.',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging & Hardware Supplies',
                'code' => 'PACKAGING',
                'description' => 'Carton boxes, polybags, hangtags, tape rolls, barcode stickers, and shipping materials.',
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous / General',
                'code' => 'MISC',
                'description' => 'Unforeseen day-to-day factory petty cash expenses and incidental operational charges.',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            ExpenseType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
