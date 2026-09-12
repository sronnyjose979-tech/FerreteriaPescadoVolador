<?php

namespace Database\Seeders;

use App\Models\Purchase_Items;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Purchase_Items::Truncate();
        Purchase_Items::factory()->count(20)->create();
    }
}
