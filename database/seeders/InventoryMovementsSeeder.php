<?php

namespace Database\Seeders;

use App\Models\Inventory_Movement;
use Illuminate\Database\Seeder;

class InventoryMovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory_Movement::Truncate();
        Inventory_Movement::factory()->count(20)->create();
    }
}
