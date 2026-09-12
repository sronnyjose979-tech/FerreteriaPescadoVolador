<?php

namespace Database\Seeders;

use App\Models\Sale_Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Sale_Item::Truncate();
        Sale_Item::factory()->count(20)->create();
    }
}
