<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        PurchaseItem::truncate();
        Purchase::truncate();
        Product::truncate();
        Category::truncate();
        Brand::truncate();
        Unit::truncate();
        Customer::truncate();
        Sale::truncate();
        SaleItem::truncate();

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
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
            CustomerSeeder::class,
            SaleSeeder::class,
            SaleItemSeeder::class,
        ]);

        //CREAMOS LOS USUARIOS
        $admin =  User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com'
        ]);
        $cajero = User::factory()->create([
            'name' => 'cajero',
            'email' => 'cajero@example.com'
        ]);
        $bodeguero = User::factory()->create([
            'name' => 'bodeguero',
            'email' => 'bodeguero@example.com'
        ]);

        //CREAMOS LOS ROLES PARA ASIGNARLOS A LOS USUARIOS
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleCajero = Role::create(['name' => 'cajero']);
        $roleBodeguero = Role::create(['name' => 'bodeguero']);

        //CREAMOS LOS PERMISOS DEL CRUD DE PRODUCT PARA  ASIGNARLOS A LOS ROLES
        $ViewPermissionProduct = Permission::create(['name' => 'view products']);
        $CreatePermissionProduct = Permission::create(['name' => 'create products']);
        $UpdatePermissionProduct = Permission::create(['name' => 'update products']);
        $DeletePermissionProduct = Permission::create(['name' => 'delete products']);

        //LE DAMOS PERMISOS COMPLETOS AL ADMIN
        $roleAdmin->givePermissionTo($ViewPermissionProduct);
        $roleAdmin->givePermissionTo($CreatePermissionProduct);
        $roleAdmin->givePermissionTo($UpdatePermissionProduct);
        $roleAdmin->givePermissionTo($DeletePermissionProduct);

        //SOLO LE DAMOS PERMISOS LIMITADOS AL CAJERO
        $roleCajero->givePermissionTo($ViewPermissionProduct);

        //SOLO LE DAMOS PERMISOS LIMITADOS AL BODEGUERO
        $roleBodeguero->givePermissionTo($ViewPermissionProduct);
        $roleBodeguero->givePermissionTo($CreatePermissionProduct);
        $roleBodeguero->givePermissionTo($UpdatePermissionProduct);

        //ASIGNAMOS EL ROLE A CADA UNO DE LOS USUARIOS
        $admin->assignRole($roleAdmin);
        $cajero->assignRole($roleCajero);
        $bodeguero->assignRole($roleBodeguero);
    }
}
