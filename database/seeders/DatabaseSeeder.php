<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Roles and permissions first
            RoleSeeder::class,
            // Packages next (merchants depend on packages)
            PackageSeeder::class,
            // Superadmin user last
            SuperAdminSeeder::class,
            // Demo data
            DemoDataSeeder::class,
        ]);
    }
}
