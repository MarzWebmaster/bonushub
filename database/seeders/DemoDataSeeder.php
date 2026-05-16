<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Merchant;
use App\Models\Customer;
use App\Models\User;
use App\Models\Branch;
use App\Models\LoyaltyRate;
use App\Models\MerchantReward;
use App\Models\PointsTransaction;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===========================
        // 1. MERCHANTS
        // ===========================
        $kopi = Merchant::create([
            'company_name' => 'Kopi A Coffee',
            'phone' => '0121112233',
            'address' => '12, Jalan SS2/72, Petaling Jaya',
            'status' => 'active',
            'package_id' => 4,
            'subscription_expiry' => now()->addYear(),
        ]);

        $baju = Merchant::create([
            'company_name' => 'Fashion B',
            'phone' => '0122223344',
            'address' => 'Lot G-01, Sunway Pyramid',
            'status' => 'active',
            'package_id' => 3,
            'subscription_expiry' => now()->addYear(),
        ]);

        $buku = Merchant::create([
            'company_name' => 'BukuCom',
            'phone' => '0123334455',
            'address' => '22, Jalan TAR, Kuala Lumpur',
            'status' => 'active',
            'package_id' => 2,
            'subscription_expiry' => now()->addYear(),
        ]);

        $this->command->info('Created 3 merchants');

        // ===========================
        // 2. BRANCHES
        // ===========================
        Branch::create(['merchant_id' => $kopi->id, 'name' => 'Kopi A SS2', 'address' => '12, Jalan SS2/72, PJ', 'phone' => '0121112233']);
        Branch::create(['merchant_id' => $kopi->id, 'name' => 'Kopi A Bangsar', 'address' => '5, Jalan Telawi, Bangsar', 'phone' => '0121112244']);
        Branch::create(['merchant_id' => $baju->id, 'name' => 'Fashion B Sunway', 'address' => 'Lot G-01 Sunway Pyramid', 'phone' => '0122223344']);
        Branch::create(['merchant_id' => $baju->id, 'name' => 'Fashion B KLCC', 'address' => 'Lot K-12 Suria KLCC', 'phone' => '0122223355']);
        Branch::create(['merchant_id' => $buku->id, 'name' => 'BukuCom HQ', 'address' => '22, Jalan TAR, KL', 'phone' => '0123334455']);

        $this->command->info('Created 5 branches');

        // ===========================
        // 3. MERCHANT ADMINS
        // ===========================
        $adminKopi = User::create([
            'name' => 'Ahmad Kopi',
            'email' => 'ahmad@kopia.com',
            'password' => Hash::make('password'),
            'phone' => '0121110011',
            'merchant_id' => $kopi->id,
            'status' => 'active',
        ]);
        $adminKopi->assignRole('Shop Admin');

        $adminBaju = User::create([
            'name' => 'Siti Fashion',
            'email' => 'siti@bajub.com',
            'password' => Hash::make('password'),
            'phone' => '0122220022',
            'merchant_id' => $baju->id,
            'status' => 'active',
        ]);
        $adminBaju->assignRole('Shop Admin');

        $adminBuku = User::create([
            'name' => 'Ali Buku',
            'email' => 'ali@bukucom.com',
            'password' => Hash::make('password'),
            'phone' => '0123330033',
            'merchant_id' => $buku->id,
            'status' => 'active',
        ]);
        $adminBuku->assignRole('Shop Admin');

        $this->command->info('Created 3 merchant admins');

        // ===========================
        // 4. STAFF
        // ===========================
        $staffKopi = User::create([
            'name' => 'Rizal Barista',
            'email' => 'rizal@kopia.com',
            'password' => Hash::make('password'),
            'phone' => '0121110044',
            'merchant_id' => $kopi->id,
            'status' => 'active',
        ]);
        $staffKopi->assignRole('Staff');

        $staffBaju = User::create([
            'name' => 'Emma Sales',
            'email' => 'emma@bajub.com',
            'password' => Hash::make('password'),
            'phone' => '0122220055',
            'merchant_id' => $baju->id,
            'status' => 'active',
        ]);
        $staffBaju->assignRole('Staff');

        $this->command->info('Created 2 staff');

        // ===========================
        // 5. CUSTOMERS
        // ===========================
        $customers = [];
        $data = [
            ['Ali Bin Ahmad', '0123456789', 'ali@gmail.com', 'Gold'],
            ['Siti Nurhaliza', '0123456790', 'siti@email.com', 'Platinum'],
            ['Raju Kumar', '0123456791', 'raju@email.com', 'Silver'],
            ['Mei Ling', '0123456792', 'mei@email.com', 'Bronze'],
            ['John Tan', '0123456793', 'john@email.com', 'Silver'],
            ['Sarah Lee', '0123456794', 'sarah@email.com', 'Basic'],
            ['Faris Hakim', '0123456795', 'faris@email.com', 'Gold'],
            ['Aina Maisarah', '0123456796', 'aina@email.com', 'Silver'],
            ['Karthik Raj', '0123456797', 'karthik@email.com', 'Bronze'],
            ['Zara Aziz', '0123456798', 'zara@email.com', 'Platinum'],
        ];

        foreach ($data as $d) {
            $c = Customer::create([
                'name' => $d[0],
                'phone' => $d[1],
                'email' => $d[2],
                'password' => Hash::make('password'),
                'tier_global' => $d[3],
                'registered_at' => now()->subDays(rand(30, 180)),
            ]);
            $customers[] = $c;
        }

        $this->command->info('Created 10 customers');

        // ===========================
        // 6. CUSTOMER-MERCHANT TIES + POINTS
        // ===========================
        $ties = [
            // [customer_idx, merchant_id_idx, points, tier]
            [0, $kopi->id, 2450, 'Gold', 1],
            [0, $baju->id, 350, 'Bronze', 1],
            [1, $kopi->id, 5800, 'Platinum', 1],
            [1, $buku->id, 1200, 'Silver', 1],
            [2, $kopi->id, 800, 'Silver', 1],
            [2, $baju->id, 1500, 'Silver', 1],
            [2, $buku->id, 200, 'Bronze', 1],
            [3, $baju->id, 150, 'Bronze', 1],
            [3, $buku->id, 2000, 'Gold', 1],
            [4, $kopi->id, 950, 'Silver', 1],
            [4, $baju->id, 3200, 'Gold', 1],
            [5, $kopi->id, 50, 'Basic', 1],
            [6, $kopi->id, 1800, 'Gold', 1],
            [6, $baju->id, 700, 'Silver', 1],
            [6, $buku->id, 2500, 'Gold', 1],
            [7, $kopi->id, 600, 'Silver', 1],
            [7, $buku->id, 450, 'Bronze', 1],
            [8, $baju->id, 180, 'Bronze', 1],
            [8, $buku->id, 800, 'Silver', 1],
            [9, $kopi->id, 4200, 'Platinum', 1],
            [9, $baju->id, 2800, 'Gold', 1],
            [9, $buku->id, 1500, 'Silver', 1],
        ];

        foreach ($ties as $t) {
            DB::table('customer_merchant')->insert([
                'customer_id' => $customers[$t[0]]->id,
                'merchant_id' => $t[1],
                'points' => $t[2],
                'tier_per_merchant' => $t[3],
                'tied_at' => now()->subDays(rand(7, 150)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Created ' . count($ties) . ' customer-merchant ties');

        // ===========================
        // 7. POINTS TRANSACTIONS
        // ===========================
        $txTypes = ['earn', 'earn', 'earn', 'redeem', 'earn'];
        $statuses = ['approved', 'approved', 'approved', 'approved', 'pending'];
        $txCount = 0;

        foreach ($ties as $t) {
            $numTx = rand(3, 8);
            for ($i = 0; $i < $numTx; $i++) {
                $type = $txTypes[array_rand($txTypes)];
                $pts = rand(10, 500);
                PointsTransaction::create([
                    'customer_id' => $customers[$t[0]]->id,
                    'merchant_id' => $t[1],
                    'type' => $type,
                    'points' => $type === 'earn' ? $pts : -$pts,
                    'amount_spent' => $type === 'earn' ? $pts * rand(1, 10) : 0,
                    'status' => $statuses[array_rand($statuses)],
                    'notes' => $type === 'earn' ? 'Purchase transaction' : 'Redemption',
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
                $txCount++;
            }
        }

        $this->command->info("Created {$txCount} points transactions");

        // ===========================
        // 8. LOYALTY RATES
        // ===========================
        LoyaltyRate::create(['merchant_id' => $kopi->id, 'rate_per_rm' => 1, 'festive_multiplier' => 2]);
        LoyaltyRate::create(['merchant_id' => $baju->id, 'rate_per_rm' => 2, 'festive_multiplier' => 3]);
        LoyaltyRate::create(['merchant_id' => $buku->id, 'rate_per_rm' => 1, 'festive_multiplier' => 1.5]);

        $this->command->info('Created 3 loyalty rates');

        // ===========================
        // 9. REWARD PRODUCTS
        // ===========================
        $rewards = [
            ['Kopi A - Warm Latte', 'Kopi A regular', 50, 100, 'self_collect', null, null, null],
            ['Kopi A - Cold Brew', 'Segar dan mantap', 75, 80, 'self_collect', null, null, null],
            ['Kopi A - Voucher RM20', '', 200, 50, 'delivery', 'merchant', null, null],
            ['Kopi A - Kopi Pack (Digital)', 'Download resepi kopi eksklusif', 30, 999, 'download', null, 'https://kopia.com/resepi.pdf', null],
            ['Fashion B - Diskaun 20%', 'Access code utk 20% off', 100, 100, 'access_code', null, null, 'FASH20'],
            ['Fashion B - Voucher RM50', '', 500, 20, 'delivery', 'customer', null, null],
            ['Fashion B - Tote Bag Eksklusif', '', 300, 30, 'self_collect', null, null, null],
            ['BukuCom - Voucher RM10', '', 100, 100, 'access_code', null, null, 'BUKU10'],
            ['BukuCom - Novel Pilihan', '', 200, 50, 'self_collect', null, null, null],
            ['BukuCom - eBook (Download)', '', 50, 200, 'download', null, 'https://bukucom.com/ebook.pdf', null],
        ];

        $rewardsConfig = [
            [$kopi->id, 'Kopi A - Warm Latte', 'Kopi A regular', 50, 100, 'self_collect', 'none', null, null],
            [$kopi->id, 'Kopi A - Cold Brew', 'Segar dan mantap', 75, 80, 'self_collect', 'none', null, null],
            [$kopi->id, 'Kopi A - Voucher RM20', '', 200, 50, 'delivery', 'merchant', null, null],
            [$kopi->id, 'Kopi A - Kopi Pack (Digital)', 'Download resepi kopi eksklusif', 30, 999, 'download', 'none', 'https://kopia.com/resepi.pdf', null],
            [$baju->id, 'Fashion B - Diskaun 20%', 'Access code utk 20% off', 100, 100, 'access_code', 'none', null, 'FASH20'],
            [$baju->id, 'Fashion B - Voucher RM50', '', 500, 20, 'delivery', 'customer', null, null],
            [$baju->id, 'Fashion B - Tote Bag Eksklusif', '', 300, 30, 'self_collect', 'none', null, null],
            [$buku->id, 'BukuCom - Voucher RM10', '', 100, 100, 'access_code', 'none', null, 'BUKU10'],
            [$buku->id, 'BukuCom - Novel Pilihan', '', 200, 50, 'self_collect', 'none', null, null],
            [$buku->id, 'BukuCom - eBook (Download)', '', 50, 200, 'download', 'none', 'https://bukucom.com/ebook.pdf', null],
        ];

        foreach ($rewardsConfig as $r) {
            MerchantReward::create([
                'merchant_id' => $r[0],
                'name' => $r[1],
                'description' => $r[2],
                'points_required' => $r[3],
                'stock_quantity' => $r[4],
                'stock_left' => $r[4],
                'claim_type' => $r[5],
                'delivery_cost' => $r[6],
                'download_url' => $r[7],
                'access_code_prefix' => $r[8],
                'status' => 'active',
            ]);
        }

        $this->command->info('Created ' . count($rewards) . ' reward products');

        $this->command->info('');
        $this->command->info('================================');
        $this->command->info('📊 DEMO DATA COMPLETE!');
        $this->command->info('================================');
        $this->command->info('');
        $this->command->info('👑 Superadmin: admin@bonushub.com / password');
        $this->command->info('🏪 Merchant:    ahmad@kopia.com / password');
        $this->command->info('🏪 Merchant:    siti@bajub.com / password');
        $this->command->info('🏪 Merchant:    ali@bukucom.com / password');
        $this->command->info('👤 Staff:      rizal@kopia.com / password');
        $this->command->info('👤 Customer:   ali@gmail.com / password');
        $this->command->info('');
    }
}
