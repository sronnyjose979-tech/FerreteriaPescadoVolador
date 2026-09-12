<?php

namespace Database\Seeders;

use App\Models\Suppliers;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Suppliers::Truncate();
        Suppliers::factory()->count(20)->create();
    }
}
