<?php

namespace Database\Seeders;

use App\Models\Purchases;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Purchases::Truncate();
        Purchases::factory()->count(20)->create();
    }
}
