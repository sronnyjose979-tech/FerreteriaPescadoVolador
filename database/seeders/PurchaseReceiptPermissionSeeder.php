<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PurchaseReceiptPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::findOrCreate('receive purchases', 'web');

        foreach (Role::whereIn('name', ['admin', 'bodeguero'])->get() as $role) {
            $role->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
