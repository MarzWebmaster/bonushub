<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Services\ReferralAttribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

class ReferralAttributionTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $referrer;
    private int $referralId;

    protected function setUp(): void
    {
        parent::setUp();
        config(['referrals.min_days' => 3, 'referrals.max_days' => 30, 'referrals.monthly_cap' => 20,
                'referrals.max_per_ip' => 2, 'referrals.max_per_hour' => 5]);

        $this->m = $this->makeMerchant();
        $this->setRates($this->m, ['referral_bonus_referrer' => 50, 'referral_bonus_referred' => 20]);
        $this->referrer = $this->makeCustomer('Rina');
        $this->joinMerchant($this->referrer, $this->m, 0);
        $this->referralId = DB::table('referrals')->insertGetId([
            'referral_code' => 'ABC123', 'customer_id' => $this->referrer, 'merchant_id' => $this->m,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function attribute(int $customerId, string $ip = '10.0.0.1', ?string $code = 'ABC123')
    {
        session(['referral_code' => $code]);
        $request = Request::create('/register', 'POST', [], [], [], ['REMOTE_ADDR' => $ip]);

        return app(ReferralAttribution::class)->attribute(Customer::findOrFail($customerId), $request);
    }

    private function signupFor(int $customerId): ?object
    {
        return DB::table('referral_signups')->where('referred_customer_id', $customerId)->first();
    }

    // ---- attribute() -------------------------------------------------------------------------------------------

    public function test_attribution_links_customer_to_referrer_and_merchant(): void
    {
        $new = $this->makeCustomer('Budi');
        DB::table('referral_clicks')->insert([
            'referral_id' => $this->referralId, 'converted' => false, 'created_at' => now(), 'updated_at' => now(),
        ] + $this->clickDefaults());

        $signup = $this->attribute($new);

        $this->assertNotNull($signup);
        $this->assertSame('pending', $signup->status);
        $this->assertNull($signup->flags);
        $this->assertDatabaseHas('customers', ['id' => $new, 'referred_by' => $this->referrer, 'referral_id' => $this->referralId]);
        $this->assertDatabaseHas('customer_merchant', ['customer_id' => $new, 'merchant_id' => $this->m, 'points' => 0]);
        $this->assertDatabaseHas('referrals', ['id' => $this->referralId, 'total_signups' => 1]);
        $this->assertDatabaseHas('referral_clicks', ['referral_id' => $this->referralId, 'converted' => 1]);
        $this->assertNull(session('referral_code'), 'the code is consumed');
    }

    public function test_no_session_code_means_no_attribution(): void
    {
        $new = $this->makeCustomer('Budi');

        $this->assertNull($this->attribute($new, '10.0.0.1', null));
        $this->assertNull($this->signupFor($new));
    }

    public function test_unknown_or_inactive_code_is_ignored(): void
    {
        $new = $this->makeCustomer('Budi');
        $this->assertNull($this->attribute($new, '10.0.0.1', 'NOPE'));

        DB::table('referrals')->where('id', $this->referralId)->update(['status' => 'inactive']);
        $this->assertNull($this->attribute($new));
        $this->assertNull($this->signupFor($new));
    }

    public function test_self_referral_is_rejected(): void
    {
        $this->assertNull($this->attribute($this->referrer));
        $this->assertSame(0, DB::table('referral_signups')->count());
    }

    public function test_same_email_in_a_different_case_counts_as_self_referral(): void
    {
        $dup = $this->makeCustomer('Rina2', 'RINA@example.test');

        $this->assertNull($this->attribute($dup));
    }

    public function test_double_submit_does_not_create_a_second_signup(): void
    {
        $new = $this->makeCustomer('Budi');

        $this->assertNotNull($this->attribute($new));
        $this->assertNull($this->attribute($new));
        $this->assertSame(1, DB::table('referral_signups')->count());
        $this->assertDatabaseHas('referrals', ['id' => $this->referralId, 'total_signups' => 1]);
    }

    public function test_third_signup_from_the_same_ip_is_flagged_duplicate_ip(): void
    {
        foreach (['A', 'B'] as $n) {
            $this->assertNull(optional($this->attribute($this->makeCustomer("User$n"), '9.9.9.9'))->flags);
        }

        $third = $this->attribute($this->makeCustomer('UserC'), '9.9.9.9');

        $this->assertSame(['duplicate_ip'], $third->flags);
    }

    public function test_sixth_signup_within_an_hour_is_flagged_velocity(): void
    {
        foreach (range(1, 5) as $i) {
            $this->attribute($this->makeCustomer("U$i"), "1.1.1.$i");
        }

        $sixth = $this->attribute($this->makeCustomer('U6'), '1.1.1.6');

        $this->assertSame(['velocity'], $sixth->flags);
    }

    public function test_raw_ip_is_never_stored(): void
    {
        $new = $this->makeCustomer('Budi');
        $this->attribute($new, '203.0.113.7');

        $hash = $this->signupFor($new)->signup_ip_hash;

        $this->assertNotSame('203.0.113.7', $hash);
        $this->assertSame(64, strlen($hash));
    }

    // ---- validatePending() -------------------------------------------------------------------------------------

    private function pendingSignup(string $name = 'Budi', int $ageDays = 5, ?array $flags = null): array
    {
        $cust = $this->makeCustomer($name);
        $this->joinMerchant($cust, $this->m, 0);
        $id = DB::table('referral_signups')->insertGetId([
            'referral_id' => $this->referralId, 'referred_customer_id' => $cust, 'status' => 'pending',
            'flags' => $flags ? json_encode($flags) : null,
            'created_at' => now()->subDays($ageDays), 'updated_at' => now()->subDays($ageDays),
        ]);

        return [$cust, $id];
    }

    public function test_signup_younger_than_min_days_is_not_touched(): void
    {
        [$cust, $id] = $this->pendingSignup('Budi', 1);
        $this->makeTx($cust, $this->m, 'earn', 30);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(['validated' => 0, 'invalidated' => 0, 'held' => 0, 'flagged' => 0], $stats);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'pending']);
    }

    public function test_engaged_signup_is_validated_and_both_sides_are_credited(): void
    {
        [$cust, $id] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 30);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['validated']);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'valid', 'points_referrer' => 50, 'points_referred' => 20]);
        $this->assertSame(50.0, $this->balance($this->referrer, $this->m));
        $this->assertSame(20.0, $this->balance($cust, $this->m));
        $this->assertDatabaseHas('points_transactions', ['customer_id' => $this->referrer, 'type' => 'earn', 'points' => 50, 'notes' => 'Referral bonus (referrer)']);
        $this->assertDatabaseHas('points_transactions', ['customer_id' => $cust, 'type' => 'earn', 'points' => 20, 'notes' => 'Referral bonus (welcome)']);
        $this->assertDatabaseHas('referrals', ['id' => $this->referralId, 'total_conversions' => 1]);
    }

    public function test_validation_is_idempotent(): void
    {
        [$cust] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 30);

        app(ReferralAttribution::class)->validatePending();
        $again = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(0, $again['validated']);
        $this->assertSame(50.0, $this->balance($this->referrer, $this->m), 'the bonus is paid exactly once');
    }

    public function test_referral_bonus_alone_does_not_count_as_engagement(): void
    {
        [$cust, $id] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 20, null, 'approved', ['notes' => 'Referral bonus (welcome)']);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['held']);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'pending']);
    }

    public function test_pending_earn_does_not_count_but_an_approved_task_submission_does(): void
    {
        [$cust, $id] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 30, null, 'pending_approval');
        $this->assertSame(1, app(ReferralAttribution::class)->validatePending()['held']);

        $this->approvedTaskSubmission($cust);

        $this->assertSame(1, app(ReferralAttribution::class)->validatePending()['validated']);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'valid']);
    }

    public function test_unengaged_signup_is_held_then_invalidated_after_max_days(): void
    {
        [, $young] = $this->pendingSignup('Young', 10);
        [, $old] = $this->pendingSignup('Old', 31);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['held']);
        $this->assertSame(1, $stats['invalidated']);
        $this->assertDatabaseHas('referral_signups', ['id' => $young, 'status' => 'pending']);
        $this->assertDatabaseHas('referral_signups', ['id' => $old, 'status' => 'invalid', 'invalid_reason' => 'not_active']);
    }

    public function test_flagged_signups_wait_for_merchant_review_and_are_never_auto_credited(): void
    {
        [$cust, $id] = $this->pendingSignup('Budi', 5, ['duplicate_ip']);
        $this->makeTx($cust, $this->m, 'earn', 30);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['flagged']);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'pending']);
        $this->assertSame(0.0, $this->balance($this->referrer, $this->m));
    }

    public function test_monthly_cap_holds_further_signups(): void
    {
        config(['referrals.monthly_cap' => 1]);
        [$a] = $this->pendingSignup('A');
        [$b, $bid] = $this->pendingSignup('B');
        $this->makeTx($a, $this->m, 'earn', 30);
        $this->makeTx($b, $this->m, 'earn', 30);

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['validated']);
        $this->assertSame(1, $stats['held']);
        $this->assertSame(50.0, $this->balance($this->referrer, $this->m));
    }

    public function test_signup_is_invalidated_when_the_referrer_left_the_merchant(): void
    {
        [$cust, $id] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 30);
        DB::table('customer_merchant')->where('customer_id', $this->referrer)->delete();

        $stats = app(ReferralAttribution::class)->validatePending();

        $this->assertSame(1, $stats['invalidated']);
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'invalid', 'invalid_reason' => 'referrer_not_member']);
    }

    public function test_zero_bonus_merchant_validates_without_ledger_rows(): void
    {
        $this->setRates($this->m, ['referral_bonus_referrer' => 0, 'referral_bonus_referred' => 0]);
        [$cust, $id] = $this->pendingSignup();
        $this->makeTx($cust, $this->m, 'earn', 30);
        $before = DB::table('points_transactions')->count();

        app(ReferralAttribution::class)->validatePending();

        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'valid']);
        $this->assertSame($before, DB::table('points_transactions')->count());
    }

    private function clickDefaults(): array
    {
        $cols = DB::getSchemaBuilder()->getColumns('referral_clicks');
        $o = [];
        foreach ($cols as $c) {
            if (!$c['nullable'] && $c['default'] === null && !$c['auto_increment'] && !in_array($c['name'], ['referral_id', 'converted', 'created_at', 'updated_at'], true)) {
                $o[$c['name']] = 'x';
            }
        }

        return $o;
    }

    private function approvedTaskSubmission(int $customerId): void
    {
        $taskId = $this->fillRow('viral_tasks', ['merchant_id' => $this->m, 'status' => 'active']);
        $this->fillRow('task_submissions', ['customer_id' => $customerId, 'task_id' => $taskId, 'status' => 'approved']);
    }

    /** Inserts a row, filling any other NOT NULL column that has no default with a dummy value. */
    private function fillRow(string $table, array $row): int
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
