<?php

namespace Database\Seeders;

use App\Models\Inventory_Movements;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryMovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory_Movements::Truncate();
        Inventory_Movements::factory()->count(20)->create();
    }
}
