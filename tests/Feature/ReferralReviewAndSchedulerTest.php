<?php

namespace Tests\Feature;

use App\Mail\GiveawayWinnerMail;
use App\Mail\PointsEarnedMail;
use App\Mail\PointsExpiryWarningMail;
use App\Mail\RewardRedeemedMail;
use App\Mail\TaskApprovedMail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

/** Merchant review of flagged referral signups, the scheduled artisan commands and the e-mail templates. */
class ReferralReviewAndSchedulerTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $other;
    private $owner;
    private int $referrer;
    private int $referralId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->m = $this->makeMerchant();
        $this->other = $this->makeMerchant(['company_name' => 'Lain']);
        $this->owner = $this->makeUser('merchant', $this->m);
        $this->referrer = $this->makeCustomer('Rina');
        $this->joinMerchant($this->referrer, $this->m);
        $this->referralId = DB::table('referrals')->insertGetId([
            'referral_code' => 'ABC123', 'customer_id' => $this->referrer, 'merchant_id' => $this->m,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function signup(?array $flags, string $status = 'pending', ?int $referralId = null): int
    {
        return DB::table('referral_signups')->insertGetId([
            'referral_id' => $referralId ?? $this->referralId,
            'referred_customer_id' => $this->makeCustomer('c' . uniqid()),
            'status' => $status,
            'flags' => $flags === null ? null : json_encode($flags),
            'created_at' => now()->subDays(5), 'updated_at' => now(),
        ]);
    }

    // ---- referral review ---------------------------------------------------------------------------------------

    public function test_review_page_lists_only_flagged_pending_signups_of_this_merchant(): void
    {
        $this->signup(['duplicate_ip']);
        $this->signup(null);                                    // not flagged
        $this->signup([]);                                      // empty flag list
        $this->signup(['velocity'], 'valid');                   // already decided
        $foreignCust = $this->makeCustomer('Foreign Referrer');
        $foreignRef = DB::table('referrals')->insertGetId([
            'referral_code' => 'FOR1', 'customer_id' => $foreignCust, 'merchant_id' => $this->other,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->signup(['duplicate_ip'], 'pending', $foreignRef);

        $res = $this->actingAs($this->owner)->get('/merchant/referral-review')->assertOk();

        $res->assertSee('Several signups from the same network')->assertDontSee('Foreign Referrer');
        $this->assertCount(1, $res->viewData('rows'));
    }

    public function test_approve_clears_the_flags_so_normal_validation_can_continue(): void
    {
        $id = $this->signup(['duplicate_ip']);

        $this->actingAs($this->owner)->post("/merchant/referral-review/$id/approve")->assertSessionHas('success');

        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'pending', 'flags' => null]);
        $this->actingAs($this->owner)->post("/merchant/referral-review/$id/approve")->assertSessionHas('error');
    }

    public function test_reject_invalidates_the_signup_and_pays_nothing(): void
    {
        $this->setRates($this->m, ['referral_bonus_referrer' => 50, 'referral_bonus_referred' => 20]);
        $id = $this->signup(['velocity']);

        $this->actingAs($this->owner)->post("/merchant/referral-review/$id/reject")->assertSessionHas('success');

        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'invalid', 'invalid_reason' => 'rejected_by_merchant']);
        $this->artisan('referrals:validate')->assertSuccessful();
        $this->assertSame(0.0, $this->balance($this->referrer, $this->m));
    }

    public function test_a_merchant_cannot_review_another_merchants_signup_and_staff_cannot_review_at_all(): void
    {
        $foreignCust = $this->makeCustomer('Foreign');
        $foreignRef = DB::table('referrals')->insertGetId([
            'referral_code' => 'FOR2', 'customer_id' => $foreignCust, 'merchant_id' => $this->other,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $id = $this->signup(['velocity'], 'pending', $foreignRef);

        $this->actingAs($this->owner)->post("/merchant/referral-review/$id/reject")->assertSessionHas('error');
        $this->assertDatabaseHas('referral_signups', ['id' => $id, 'status' => 'pending']);

        $staff = $this->makeUser('staff', $this->m);
        $own = $this->signup(['velocity']);
        $this->actingAs($staff)->post("/merchant/referral-review/$own/approve")->assertForbidden();
    }

    // ---- scheduled commands ------------------------------------------------------------------------------------

    public function test_all_maintenance_commands_are_scheduled(): void
    {
        $commands = collect(app(Schedule::class)->events())->map(fn ($e) => $e->command)->implode("\n");

        foreach (['referrals:validate', 'points:expire', 'tiers:refresh', 'giveaways:refresh-scores'] as $name) {
            $this->assertStringContainsString($name, $commands, "$name is not scheduled");
        }
    }

    public function test_points_expire_command_does_nothing_while_the_feature_is_off(): void
    {
        config(['points.expiry_enabled' => false]);
        $c = $this->makeCustomer('Ali');
        $this->joinMerchant($c, $this->m, 100, 'Basic', now()->subYears(3));

        $this->artisan('points:expire')->expectsOutputToContain('disabled')->assertSuccessful();

        $this->assertSame(100.0, $this->balance($c, $this->m));
    }

    public function test_points_expire_dry_run_works_even_while_off_and_a_real_run_expires_when_on(): void
    {
        $c = $this->makeCustomer('Ali');
        $this->joinMerchant($c, $this->m, 100, 'Basic', now()->subYears(3));

        config(['points.expiry_enabled' => false]);
        $this->artisan('points:expire', ['--dry' => true])->expectsOutputToContain('Would expire 1')->assertSuccessful();
        $this->assertSame(100.0, $this->balance($c, $this->m));

        config(['points.expiry_enabled' => true]);
        $this->artisan('points:expire')->expectsOutputToContain('Expired 1')->assertSuccessful();
        $this->assertSame(0.0, $this->balance($c, $this->m));
    }

    public function test_tiers_refresh_command_upgrades_and_supports_dry_run(): void
    {
        $this->makeTier($this->m, 'Basic', 0);
        $this->makeTier($this->m, 'Silver', 100);
        $c = $this->makeCustomer('Ali');
        $this->joinMerchant($c, $this->m, 0, 'Basic');
        $this->makeTx($c, $this->m, 'earn', 150);

        $this->artisan('tiers:refresh', ['--dry' => true])->expectsOutputToContain('Would upgrade 1')->assertSuccessful();
        $this->assertSame('Basic', $this->tierOf($c, $this->m));

        $this->artisan('tiers:refresh')->expectsOutputToContain('Upgraded 1')->assertSuccessful();
        $this->assertSame('Silver', $this->tierOf($c, $this->m));
    }

    public function test_referrals_validate_command_reports_what_it_did(): void
    {
        $this->setRates($this->m, ['referral_bonus_referrer' => 10, 'referral_bonus_referred' => 5]);
        $cust = $this->makeCustomer('Budi');
        $this->joinMerchant($cust, $this->m);
        $this->makeTx($cust, $this->m, 'earn', 30);
        DB::table('referral_signups')->insert([
            'referral_id' => $this->referralId, 'referred_customer_id' => $cust, 'status' => 'pending',
            'created_at' => now()->subDays(5), 'updated_at' => now(),
        ]);

        $this->artisan('referrals:validate')->expectsOutputToContain('1 valid')->assertSuccessful();
        $this->assertSame(10.0, $this->balance($this->referrer, $this->m));
    }

    public function test_giveaway_score_refresh_command_runs(): void
    {
        $this->artisan('giveaways:refresh-scores')->expectsOutputToContain('Giveaway scores refreshed')->assertSuccessful();
    }

    // ---- e-mail templates --------------------------------------------------------------------------------------

    public function test_every_mailable_renders_with_its_data_and_subject(): void
    {
        $cases = [
            [new PointsEarnedMail('Ali', 120, 450, 'Kedai Test', 'QR scan'), 'Kedai Test', ['Ali', '120']],
            [new RewardRedeemedMail('Ali', 'Kopi Percuma', 100, 'Kedai Test'), 'Kedai Test', ['Kopi Percuma']],
            [new TaskApprovedMail('Ali', 'Share post', 50, 'Kedai Test'), 'Kedai Test', ['Share post']],
            [new PointsExpiryWarningMail('Ali', 'Kedai Test', 300, '31/12/2026'), 'Kedai Test', ['300', '31/12/2026']],
            [new GiveawayWinnerMail('Ali', 'Hamper', 'Raya Giveaway', 'Kedai Test', '30/10/2026'), 'Raya Giveaway', ['Hamper', '30/10/2026']],
            [new GiveawayWinnerMail('Ali', 'Hamper', 'Raya Giveaway', 'Kedai Test', null, true), 'Raya Giveaway', ['Hamper']],
        ];

        foreach ($cases as [$mail, $subjectPart, $bodyParts]) {
            $this->assertStringContainsString($subjectPart, $mail->envelope()->subject);
            $html = $mail->render();
            foreach ($bodyParts as $part) {
                $this->assertStringContainsString($part, $html, get_class($mail) . " is missing '$part'");
            }
        }
    }
}
