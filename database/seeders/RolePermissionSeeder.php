<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Crea los roles del sistema (admin, cajero y bodeguero) y sus permisos por recurso.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // CREAMOS LOS ROLES PARA ASIGNARLOS A LOS USUARIOS
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
            'inventory-movements' => ['view', 'create', 'update', 'delete'],
            'payments' => ['view', 'create', 'update', 'delete'],
        ];
        // EN ESTA PARTE CREAMOS TODOS LOS PERMISOS CREADOS EN LA SECCION DE ARRIBA
        foreach ($permissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    // AQUI SE COLOCA $ACTION COMO 'VIEW' Y EL RESOURCE COMO 'PRODUCTS'
                    'name' => "$action $resource",
                ]);
            }
        }
        // LE DAMOS PERMISOS COMPLETOS AL ADMIN
        $roleAdmin->givePermissionTo(Permission::all());

        // SOLO LE DAMOS PERMISOS LIMITADOS AL CAJERO
        $roleCajero->givePermissionTo([
            'view products',
            'view sales',
            'create sales',
            'view customers',
        ]);

        // SOLO LE DAMOS PERMISOS LIMITADOS AL BODEGUERO
        $roleBodeguero->givePermissionTo([
            'view products',
            'create products',
            'update products',
            'view purchases',
            'view suppliers',
            'create suppliers',
            'update suppliers',
        ]);
    }
}
