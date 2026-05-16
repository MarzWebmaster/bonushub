<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the Superadmin role exists
        $role = Role::firstOrCreate(['name' => 'Superadmin']);

        // Create the superadmin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@bonushub.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        // Assign the Superadmin role
        $user->assignRole($role);

        $this->command->info('Superadmin user created: admin@bonushub.com / password');
    }
}
