<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
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
        InventoryMovement::truncate();
        Payment::truncate();
        SaleItem::truncate();
        Sale::truncate();
        PurchaseItem::truncate();
        Purchase::truncate();
        Product::truncate();
        Category::truncate();
        Brand::truncate();
        Unit::truncate();
        Customer::truncate();
        Supplier::truncate();

        // CREAMOS LOS USUARIOS
        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
        ]);
        $cajero = User::factory()->create([
            'name' => 'cajero',
            'email' => 'cajero@example.com',
        ]);
        $bodeguero = User::factory()->create([
            'name' => 'bodeguero',
            'email' => 'bodeguero@example.com',
        ]);

        $this->call([

            CategorySeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
            CustomerSeeder::class,
            SaleSeeder::class,
            SaleItemSeeder::class,
            PaymentSeeder::class,
            InventoryMovementSeeder::class,
        ]);

        $this->call(RolePermissionSeeder::class);

        // ASIGNAMOS EL ROLE A CADA UNO DE LOS USUARIOS
        $admin->assignRole('admin');
        $cajero->assignRole('cajero');
        $bodeguero->assignRole('bodeguero');
    }
}
