<?php

namespace Database\Seeders;

use App\Models\Order_Item;
use Illuminate\Database\Seeder;

class OrderItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order_Item::Truncate();
        Order_Item::factory()->count(20)->create();
    }
}
