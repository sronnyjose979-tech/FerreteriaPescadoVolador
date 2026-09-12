<?php

namespace Database\Seeders;

use App\Models\PurchaseItem;
use Illuminate\Database\Seeder;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseItem::Truncate();
        PurchaseItem::factory()->count(20)->create();
    }
}
