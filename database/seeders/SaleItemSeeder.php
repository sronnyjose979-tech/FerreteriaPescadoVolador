<?php

namespace Database\Seeders;

use App\Models\SaleItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         SaleItem::Truncate();
        SaleItem::factory()->count(20)->create();
    }
}
