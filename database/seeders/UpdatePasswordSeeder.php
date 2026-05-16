<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePasswordSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->where('email', 'john.doe@example.com')->update(['password' => bcrypt('password123')]);
        DB::table('users')->where('email', 'jane.smith@example.com')->update(['password' => bcrypt('securepass')]);
        DB::table('users')->where('email', 'admin@admin.com')->update(['password' => bcrypt('admin')]);
    }
}
