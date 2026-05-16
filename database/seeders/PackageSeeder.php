<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Free',
                'price' => 0,
                'branch_limit' => 1,
                'staff_limit' => 2,
                'giveaway_limit' => 0,
                'task_limit' => 0,
                'features' => json_encode([
                    'basic_points_tracking' => true,
                    'manual_entry' => true,
                ]),
                'is_default' => true,
            ],
            [
                'name' => 'Starter',
                'price' => 49,
                'branch_limit' => 3,
                'staff_limit' => 10,
                'giveaway_limit' => 50,
                'task_limit' => 10,
                'features' => json_encode([
                    'basic_points_tracking' => true,
                    'manual_entry' => true,
                    'csv_import' => true,
                    'basic_reports' => true,
                    'giveaway_management' => true,
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Advance',
                'price' => 129,
                'branch_limit' => 10,
                'staff_limit' => 50,
                'giveaway_limit' => 200,
                'task_limit' => 50,
                'features' => json_encode([
                    'basic_points_tracking' => true,
                    'manual_entry' => true,
                    'csv_import' => true,
                    'api_integration' => true,
                    'advanced_reports' => true,
                    'giveaway_management' => true,
                    'task_automation' => true,
                    'multi_branch' => true,
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Enterprise',
                'price' => 299,
                'branch_limit' => -1, // unlimited
                'staff_limit' => -1, // unlimited
                'giveaway_limit' => -1, // unlimited
                'task_limit' => -1, // unlimited
                'features' => json_encode([
                    'basic_points_tracking' => true,
                    'manual_entry' => true,
                    'csv_import' => true,
                    'api_integration' => true,
                    'advanced_reports' => true,
                    'giveaway_management' => true,
                    'task_automation' => true,
                    'multi_branch' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'dedicated_account_manager' => true,
                ]),
                'is_default' => false,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }

        $this->command->info('Default packages seeded successfully.');
    }
}
