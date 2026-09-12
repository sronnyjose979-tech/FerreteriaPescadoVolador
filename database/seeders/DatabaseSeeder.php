<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\SupplierSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::truncate();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            PurchasesSeeder::class,
        ]);
    }
}
