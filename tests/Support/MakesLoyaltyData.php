<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

/**
 * Plain query-builder fixtures: the loyalty tables are written to directly by the services under test, and
 * going through the models would only test their $fillable lists.
 */
trait MakesLoyaltyData
{
    protected function makeMerchant(array $o = []): int
    {
        return DB::table('merchants')->insertGetId($o + [
            'company_name' => 'Kedai Test',
            'status'       => 'active',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    protected function makeCustomer(string $name = 'Ali', ?string $email = null): int
    {
        return DB::table('customers')->insertGetId([
            'name'       => $name,
            'email'      => $email ?? strtolower($name) . '@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function joinMerchant(int $customerId, int $merchantId, float $points = 0, string $tier = 'Basic', $tiedAt = null): int
    {
        return DB::table('customer_merchant')->insertGetId([
            'customer_id'       => $customerId,
            'merchant_id'       => $merchantId,
            'points'            => $points,
            'tier_per_merchant' => $tier,
            'tied_at'           => $tiedAt ?? now(),
            'created_at'        => $tiedAt ?? now(),
            'updated_at'        => now(),
        ]);
    }

    protected function makeTx(int $customerId, int $merchantId, string $type, float $points, $at = null, string $status = 'approved', array $extra = []): int
    {
        return DB::table('points_transactions')->insertGetId($extra + [
            'customer_id' => $customerId,
            'merchant_id' => $merchantId,
            'type'        => $type,
            'points'      => $points,
            'status'      => $status,
            'notes'       => 'Points added by staff',   // real code paths always write a note
            'created_at'  => $at ?? now(),
            'updated_at'  => $at ?? now(),
        ]);
    }

    protected function makeTier(int $merchantId, string $name, int $min): void
    {
        DB::table('merchant_tiers')->insert([
            'merchant_id' => $merchantId, 'tier_name' => $name, 'min_points' => $min,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function setRates(int $merchantId, array $o = []): void
    {
        DB::table('loyalty_rates')->updateOrInsert(['merchant_id' => $merchantId], $o + ['created_at' => now(), 'updated_at' => now()]);
    }

    protected function balance(int $customerId, int $merchantId): float
    {
        return (float) DB::table('customer_merchant')->where('customer_id', $customerId)->where('merchant_id', $merchantId)->value('points');
    }

    protected function tierOf(int $customerId, int $merchantId): string
    {
        return (string) DB::table('customer_merchant')->where('customer_id', $customerId)->where('merchant_id', $merchantId)->value('tier_per_merchant');
    }

    protected function makeUser(string $role, ?int $merchantId = null, array $o = []): \App\Models\User
    {
        \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
        $id = DB::table('users')->insertGetId($o + [
            'name'        => ucfirst($role) . ' ' . uniqid(),
            'email'       => $role . uniqid() . '@example.test',
            'password'    => 'x',
            'role'        => $role,
            'merchant_id' => $merchantId,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $user = \App\Models\User::findOrFail($id);
        $user->assignRole($role);

        return $user;
    }

    protected function makeReward(int $merchantId, float $points = 100, int $stock = 5, array $o = []): int
    {
        return DB::table('merchant_rewards')->insertGetId($o + [
            'merchant_id'     => $merchantId,
            'name'            => 'Kopi Percuma',
            'points_required' => $points,
            'stock_quantity'  => $stock,
            'stock_left'      => $stock,
            'claim_type'      => 'self_collect',
            'status'          => 'active',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    protected function makeRedemption(int $customerId, int $merchantId, int $rewardId, array $o = []): int
    {
        return DB::table('redemptions')->insertGetId($o + [
            'customer_id'  => $customerId,
            'merchant_id'  => $merchantId,
            'reward_id'    => $rewardId,
            'points_used'  => 100,
            'claim_method' => 'self_collect',
            'status'       => 'approved',
            'claim_code'   => strtoupper(substr(md5(uniqid()), 0, 8)),
            'quantity'     => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    /** Inserts a row, filling any other NOT NULL column that has no default with a dummy value. */
    protected function fillRow(string $table, array $row): int
    {
        $row += ['created_at' => now(), 'updated_at' => now()];
        foreach (DB::getSchemaBuilder()->getColumns($table) as $c) {
            if (!$c['nullable'] && $c['default'] === null && !$c['auto_increment'] && !array_key_exists($c['name'], $row)) {
                $row[$c['name']] = str_contains($c['type'], 'int') ? 1 : (str_contains($c['type'], 'date') ? now() : 'x');
            }
        }

        return DB::table($table)->insertGetId($row);
    }
}
