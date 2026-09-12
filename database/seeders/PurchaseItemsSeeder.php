<?php

namespace Database\Seeders;

use App\Models\Purchase_Item;
use Illuminate\Database\Seeder;

class PurchaseItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Purchase_Item::Truncate();
        Purchase_Item::factory()->count(20)->create();
    }
}
