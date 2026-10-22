<?php

namespace Tests\Unit;

use App\Services\TierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

class TierServiceTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->m = $this->makeMerchant();
        $this->c = $this->makeCustomer();
        $this->makeTier($this->m, 'Basic', 0);
        $this->makeTier($this->m, 'Silver', 500);
        $this->makeTier($this->m, 'Gold', 2000);
    }

    public function test_earned_rolling_counts_only_approved_earn_rows_from_the_last_12_months(): void
    {
        $this->joinMerchant($this->c, $this->m);
        $this->makeTx($this->c, $this->m, 'earn', 300);
        $this->makeTx($this->c, $this->m, 'earn', 100, now()->subMonths(11));
        $this->makeTx($this->c, $this->m, 'earn', 999, now()->subMonths(13));           // too old
        $this->makeTx($this->c, $this->m, 'earn', 999, null, 'pending_approval');       // not approved
        $this->makeTx($this->c, $this->m, 'redeem', -50);                               // redeeming never lowers it

        $this->assertSame(400.0, app(TierService::class)->earnedRolling($this->c, $this->m));
    }

    public function test_voided_earn_rows_do_not_count(): void
    {
        $this->joinMerchant($this->c, $this->m);
        $earn = $this->makeTx($this->c, $this->m, 'earn', 800);
        $this->makeTx($this->c, $this->m, 'void', -800, null, 'approved', ['void_of_id' => $earn]);

        $this->assertSame(0.0, app(TierService::class)->earnedRolling($this->c, $this->m));
    }

    public function test_refresh_upgrades_to_the_highest_qualifying_tier_and_notifies(): void
    {
        $this->joinMerchant($this->c, $this->m);
        $userId = DB::table('users')->insertGetId([
            'name' => 'Ali', 'email' => 'ali@example.test', 'password' => 'x', 'role' => 'customer',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->makeTx($this->c, $this->m, 'earn', 2500);

        $r = app(TierService::class)->refresh($this->c, $this->m);

        $this->assertSame(['from' => 'Basic', 'to' => 'Gold'], $r);
        $this->assertSame('Gold', $this->tierOf($this->c, $this->m));
        $this->assertDatabaseHas('in_app_notifications', ['user_id' => $userId]);
    }

    public function test_refresh_never_downgrades_by_default(): void
    {
        $this->joinMerchant($this->c, $this->m, 0, 'Gold');
        $this->makeTx($this->c, $this->m, 'earn', 100);

        $this->assertNull(app(TierService::class)->refresh($this->c, $this->m));
        $this->assertSame('Gold', $this->tierOf($this->c, $this->m));
    }

    public function test_refresh_with_allow_downgrade_moves_down(): void
    {
        $this->joinMerchant($this->c, $this->m, 0, 'Gold');
        $this->makeTx($this->c, $this->m, 'earn', 600);

        $r = app(TierService::class)->refresh($this->c, $this->m, true);

        $this->assertSame(['from' => 'Gold', 'to' => 'Silver'], $r);
    }

    public function test_unknown_legacy_tier_name_is_left_alone(): void
    {
        $this->joinMerchant($this->c, $this->m, 0, 'regular');
        $this->makeTx($this->c, $this->m, 'earn', 5000);

        $this->assertNull(app(TierService::class)->refresh($this->c, $this->m));
        $this->assertSame('regular', $this->tierOf($this->c, $this->m));
    }

    public function test_nothing_happens_without_tiers_or_membership(): void
    {
        $other = $this->makeMerchant(['company_name' => 'No Tiers']);
        $this->joinMerchant($this->c, $other);
        $this->makeTx($this->c, $other, 'earn', 9999);

        $this->assertNull(app(TierService::class)->refresh($this->c, $other));      // merchant has no tiers
        $this->assertNull(app(TierService::class)->refresh($this->c, $this->m));    // customer is not a member
    }

    public function test_refresh_all_dry_run_counts_without_writing(): void
    {
        $this->joinMerchant($this->c, $this->m);
        $this->makeTx($this->c, $this->m, 'earn', 700);

        $this->assertSame(1, app(TierService::class)->refreshAll(true));
        $this->assertSame('Basic', $this->tierOf($this->c, $this->m));

        $this->assertSame(1, app(TierService::class)->refreshAll());
        $this->assertSame('Silver', $this->tierOf($this->c, $this->m));
    }
}
