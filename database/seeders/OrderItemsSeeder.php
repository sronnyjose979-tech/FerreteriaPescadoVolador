<?php

namespace Database\Seeders;

use App\Models\Order_Items;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order_Items::Truncate();
        Order_Items::factory()->count(20)->create();
    }
}
