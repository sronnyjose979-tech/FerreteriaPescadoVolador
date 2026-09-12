<?php

namespace Database\Seeders;

use App\Models\Sale_Items;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Sale_Items::Truncate();
        Sale_Items::factory()->count(20)->create();
    }
}
