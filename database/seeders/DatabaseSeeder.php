<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Payment;
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
        Payment::truncate();

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
            PaymentSeeder::class,
            InventoryMovementSeeder::class,
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

        /*AL HACER LOS PERMISOS DE LA MANERA TRADICIONAL VISTA EN CLASE, SE HACE DEMASIADO EXTENSA LA CREACION DE PERMISOS
        ES POR ESO QUE AGREGUE ESTA MANERA DE CREAR PERMISOS PARA TODAS LOS MODELOS*/

        $permissions = [
            'products' => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'customers' => ['view', 'create', 'update', 'delete'],
            'purchases' => ['view', 'create', 'update', 'delete'],
            'purchase-items' => ['view', 'create', 'update', 'delete'],
            'sales' => ['view', 'create', 'update', 'delete'],
            'sale-items' => ['view', 'create', 'update', 'delete'],
            'categories' => ['view', 'create', 'update', 'delete'],
            'brands' => ['view', 'create', 'update', 'delete'],
            'units' => ['view', 'create', 'update', 'delete'],
        ];
        //EN ESTA PARTE CREAMOS TODOS LOS PERMISOS CREADOS EN LA SECCION DE ARRIBA
        foreach ($permissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    //AQUI SE COLOCA $ACTION COMO 'VIEW' Y EL RESOURCE COMO 'PRODUCTS'
                    'name' => "$action $resource"
                ]);
            }
        }
        //LE DAMOS PERMISOS COMPLETOS AL ADMIN
        $roleAdmin->givePermissionTo(Permission::all());

        //SOLO LE DAMOS PERMISOS LIMITADOS AL CAJERO
        $roleCajero->givePermissionTo([
            'view products',
            'view sales',
            'create sales',
        ]);

        //SOLO LE DAMOS PERMISOS LIMITADOS AL BODEGUERO
        $roleBodeguero->givePermissionTo([
            'view products',
            'create products',
            'update products',
            'view suppliers',
            'create suppliers',
            'update suppliers',
        ]);


        //ASIGNAMOS EL ROLE A CADA UNO DE LOS USUARIOS
        $admin->assignRole($roleAdmin);
        $cajero->assignRole($roleCajero);
        $bodeguero->assignRole($roleBodeguero);
    }
}
