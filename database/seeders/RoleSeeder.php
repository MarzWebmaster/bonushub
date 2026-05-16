<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        Role::create(['name' => 'Superadmin']);
        Role::create(['name' => 'Shop Admin']);
        Role::create(['name' => 'Staff']);
        Role::create(['name' => 'Customer']);

        // Create permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage points']);
        Permission::create(['name' => 'manage redemptions']);
        Permission::create(['name' => 'view reports']);

        // Assign permissions to roles
        $superadmin = Role::findByName('Superadmin');
        $superadmin->givePermissionTo(Permission::all());

        $shopAdmin = Role::findByName('Shop Admin');
        $shopAdmin->givePermissionTo(['manage users', 'manage points', 'manage redemptions']);

        $staff = Role::findByName('Staff');
        $staff->givePermissionTo(['manage points', 'manage redemptions']);

        $customer = Role::findByName('Customer');
        $customer->givePermissionTo('view reports');
    }
}
