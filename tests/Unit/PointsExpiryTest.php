<?php

namespace Tests\Unit;

use App\Mail\PointsExpiryWarningMail;
use App\Services\PointsExpiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

class PointsExpiryTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $c;

    protected function setUp(): void
    {
        parent::setUp();
        config(['points.default_months' => 12, 'points.warn_days' => 30]);
        $this->m = $this->makeMerchant();
        $this->c = $this->makeCustomer();
    }

    public function test_months_for_uses_merchant_override_else_default(): void
    {
        $this->assertSame(12, (new PointsExpiry())->monthsFor($this->m));

        $this->setRates($this->m, ['points_expiry_months' => 6]);
        $this->assertSame(6, (new PointsExpiry())->monthsFor($this->m));

        $this->setRates($this->m, ['points_expiry_months' => 0]);
        $this->assertSame(0, (new PointsExpiry())->monthsFor($this->m));
    }

    public function test_last_activity_ignores_void_expired_and_pending_rows(): void
    {
        $joined = now()->subMonths(10);
        $this->makeTx($this->c, $this->m, 'earn', 10, now()->subMonths(8));
        $this->makeTx($this->c, $this->m, 'earn', 10, now()->subMonths(1), 'pending_approval');
        $this->makeTx($this->c, $this->m, 'expired', -5, now()->subMonths(1));

        $last = (new PointsExpiry())->lastActivity($this->c, $this->m, $joined);

        $this->assertTrue($last->isSameDay(now()->subMonths(8)));
    }

    public function test_member_without_transactions_counts_from_join_date(): void
    {
        $joined = now()->subMonths(5);

        $last = (new PointsExpiry())->lastActivity($this->c, $this->m, $joined);

        $this->assertTrue($last->isSameDay($joined));
    }

    public function test_lapse_at_is_null_when_merchant_never_expires(): void
    {
        $this->setRates($this->m, ['points_expiry_months' => 0]);

        $this->assertNull((new PointsExpiry())->lapseAt($this->c, $this->m, now()));
    }

    public function test_inactive_balance_is_expired_with_one_ledger_row(): void
    {
        $this->joinMerchant($this->c, $this->m, 250, 'Basic', now()->subMonths(14));
        $this->makeTx($this->c, $this->m, 'earn', 250, now()->subMonths(13));

        $r = (new PointsExpiry())->run();

        $this->assertSame(1, $r['expired']);
        $this->assertSame(250.0, $r['points']);
        $this->assertSame(0.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', [
            'customer_id' => $this->c, 'merchant_id' => $this->m, 'type' => 'expired', 'points' => -250, 'status' => 'approved',
        ]);
        // history is never rewritten
        $this->assertDatabaseHas('points_transactions', ['type' => 'earn', 'points' => 250]);
    }

    public function test_recent_activity_keeps_the_points(): void
    {
        $this->joinMerchant($this->c, $this->m, 100, 'Basic', now()->subMonths(20));
        $this->makeTx($this->c, $this->m, 'earn', 10, now()->subMonths(2));

        $r = (new PointsExpiry())->run();

        $this->assertSame(['expired' => 0, 'points' => 0.0, 'warned' => 0], $r);
        $this->assertSame(100.0, $this->balance($this->c, $this->m));
    }

    public function test_dry_run_reports_but_changes_nothing(): void
    {
        $this->joinMerchant($this->c, $this->m, 80, 'Basic', now()->subMonths(14));

        $r = (new PointsExpiry())->run(true);

        $this->assertSame(1, $r['expired']);
        $this->assertSame(80.0, $r['points']);
        $this->assertSame(80.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseMissing('points_transactions', ['type' => 'expired']);
    }

    public function test_merchant_with_expiry_zero_is_skipped(): void
    {
        $this->setRates($this->m, ['points_expiry_months' => 0]);
        $this->joinMerchant($this->c, $this->m, 80, 'Basic', now()->subYears(5));

        $this->assertSame(0, (new PointsExpiry())->run()['expired']);
        $this->assertSame(80.0, $this->balance($this->c, $this->m));
    }

    public function test_zero_balance_is_not_expired(): void
    {
        $this->joinMerchant($this->c, $this->m, 0, 'Basic', now()->subYears(3));

        $this->assertSame(0, (new PointsExpiry())->run()['expired']);
        $this->assertDatabaseMissing('points_transactions', ['type' => 'expired']);
    }

    public function test_customer_is_warned_once_before_the_lapse(): void
    {
        Mail::fake();
        // last activity 11 months + 10 days ago: lapses in ~20 days, inside the 30-day warning window
        $this->joinMerchant($this->c, $this->m, 120, 'Basic', now()->subYears(2));
        $this->makeTx($this->c, $this->m, 'earn', 120, now()->subMonths(11)->subDays(10));

        $first = (new PointsExpiry())->run();
        $second = (new PointsExpiry())->run();

        $this->assertSame(1, $first['warned']);
        $this->assertSame(0, $first['expired']);
        $this->assertSame(0, $second['warned']);
        $this->assertSame(120.0, $this->balance($this->c, $this->m));
        Mail::assertQueued(PointsExpiryWarningMail::class, 1);
    }

    public function test_new_activity_after_a_warning_allows_a_new_warning_later(): void
    {
        Mail::fake();
        $cm = $this->joinMerchant($this->c, $this->m, 120, 'Basic', now()->subYears(2));
        $this->makeTx($this->c, $this->m, 'earn', 60, now()->subMonths(11)->subDays(10));
        (new PointsExpiry())->run();

        // activity after the warning was sent, which itself later drifts into the warning window
        DB::table('customer_merchant')->where('id', $cm)->update(['expiry_warned_at' => now()->subYear()->subDays(60)]);

        $this->assertSame(1, (new PointsExpiry())->run()['warned']);
    }

    public function test_expiry_notifies_the_customer_in_app(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Ali', 'email' => 'ali@example.test', 'password' => 'x', 'role' => 'customer',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->joinMerchant($this->c, $this->m, 50, 'Basic', now()->subMonths(14));

        (new PointsExpiry())->run();

        $this->assertDatabaseHas('in_app_notifications', ['user_id' => $userId, 'title' => '⌛ Mata telah tamat tempoh']);
    }
}
