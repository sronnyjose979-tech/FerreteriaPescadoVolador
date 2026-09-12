<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryMovement::Truncate();
        InventoryMovement::factory()->count(20)->create();
    }
}
