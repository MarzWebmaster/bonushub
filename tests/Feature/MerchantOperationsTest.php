<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

/** Merchant-side flows added in the hardening patches: redemptions, point approvals, QR claims, read-only mode, private documents, login lockout. */
class MerchantOperationsTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $other;
    private int $c;
    private $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->m = $this->makeMerchant();
        $this->other = $this->makeMerchant(['company_name' => 'Kedai Lain']);
        $this->c = $this->makeCustomer('Ali');
        $this->joinMerchant($this->c, $this->m, 300);
        $this->owner = $this->makeUser('merchant', $this->m);
    }

    // ---- redemptions: complete / cancel ------------------------------------------------------------------------

    public function test_complete_marks_a_waiting_redemption_handed_over_once(): void
    {
        $reward = $this->makeReward($this->m);
        $r = $this->makeRedemption($this->c, $this->m, $reward);

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/complete")->assertSessionHas('success');
        $this->assertDatabaseHas('redemptions', ['id' => $r, 'status' => 'completed', 'staff_id' => $this->owner->id]);

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/complete")->assertSessionHas('error');
    }

    public function test_cancel_refunds_points_restores_stock_and_links_the_void_to_the_redeem_row(): void
    {
        $reward = $this->makeReward($this->m, 100, 4);
        $redeemTx = $this->makeTx($this->c, $this->m, 'redeem', -100);
        $r = $this->makeRedemption($this->c, $this->m, $reward, ['points_used' => 100, 'quantity' => 2, 'points_transaction_id' => $redeemTx]);

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/cancel")->assertSessionHas('success');

        $this->assertSame(400.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('merchant_rewards', ['id' => $reward, 'stock_left' => 6]);
        $this->assertDatabaseHas('redemptions', ['id' => $r, 'status' => 'cancelled']);
        $this->assertDatabaseHas('points_transactions', ['type' => 'void', 'points' => 100, 'void_of_id' => $redeemTx]);
    }

    public function test_cancel_twice_refunds_only_once(): void
    {
        $reward = $this->makeReward($this->m);
        $r = $this->makeRedemption($this->c, $this->m, $reward, ['points_used' => 100]);

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/cancel");
        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/cancel")->assertSessionHas('error');

        $this->assertSame(400.0, $this->balance($this->c, $this->m));
    }

    public function test_completed_redemption_cannot_be_cancelled(): void
    {
        $reward = $this->makeReward($this->m);
        $r = $this->makeRedemption($this->c, $this->m, $reward, ['status' => 'completed']);

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/cancel")->assertSessionHas('error');

        $this->assertSame(300.0, $this->balance($this->c, $this->m));
    }

    public function test_a_merchant_cannot_touch_another_merchants_redemption(): void
    {
        $foreignCustomer = $this->makeCustomer('Other');
        $this->joinMerchant($foreignCustomer, $this->other, 0);
        $r = $this->makeRedemption($foreignCustomer, $this->other, $this->makeReward($this->other));

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/complete")->assertSessionHas('error');
        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/cancel")->assertSessionHas('error');
        $this->assertDatabaseHas('redemptions', ['id' => $r, 'status' => 'approved']);
    }

    public function test_staff_cannot_use_the_merchant_redemption_screen(): void
    {
        $staff = $this->makeUser('staff', $this->m);
        $r = $this->makeRedemption($this->c, $this->m, $this->makeReward($this->m));

        $this->actingAs($staff)->post("/merchant/redemptions/$r/complete")->assertForbidden();
    }

    public function test_redemption_list_defaults_to_waiting_ones_and_is_scoped_to_the_merchant(): void
    {
        $reward = $this->makeReward($this->m);
        $this->makeRedemption($this->c, $this->m, $reward, ['claim_code' => 'WAITING1']);
        $this->makeRedemption($this->c, $this->m, $reward, ['claim_code' => 'DONE0001', 'status' => 'completed']);
        $fc = $this->makeCustomer('Other');
        $this->makeRedemption($fc, $this->other, $this->makeReward($this->other), ['claim_code' => 'FOREIGN1']);

        $this->actingAs($this->owner)->get('/merchant/redemptions')
            ->assertOk()->assertSee('WAITING1')->assertDontSee('DONE0001')->assertDontSee('FOREIGN1');
    }

    // ---- point approvals ---------------------------------------------------------------------------------------

    public function test_approving_a_pending_claim_credits_the_balance_once(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 120, null, 'pending_approval');

        $this->actingAs($this->owner)->post("/merchant/points/approve/$tx")->assertSessionHas('success');
        $this->assertSame(420.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', ['id' => $tx, 'status' => 'approved']);

        $this->actingAs($this->owner)->post("/merchant/points/approve/$tx")->assertNotFound();
        $this->assertSame(420.0, $this->balance($this->c, $this->m));
    }

    public function test_approval_is_atomic_if_crediting_the_balance_fails(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 120, null, 'pending_approval');
        DB::unprepared("CREATE TRIGGER fail_balance BEFORE UPDATE ON customer_merchant BEGIN SELECT RAISE(ABORT, 'boom'); END");

        $this->actingAs($this->owner)->post("/merchant/points/approve/$tx")->assertStatus(500);

        $this->assertDatabaseHas('points_transactions', ['id' => $tx, 'status' => 'pending_approval']);
        $this->assertSame(300.0, $this->balance($this->c, $this->m));
    }

    public function test_rejecting_a_pending_claim_leaves_the_balance_alone(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 120, null, 'pending_approval');

        $this->actingAs($this->owner)->post("/merchant/points/reject/$tx", ['reason' => 'Tiada resit'])->assertSessionHas('success');

        $this->assertSame(300.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', ['id' => $tx, 'status' => 'rejected', 'notes' => 'Tiada resit']);
    }

    public function test_a_merchant_cannot_approve_another_merchants_claim(): void
    {
        $fc = $this->makeCustomer('Other');
        $this->joinMerchant($fc, $this->other, 0);
        $tx = $this->makeTx($fc, $this->other, 'earn', 50, null, 'pending_approval');

        $this->actingAs($this->owner)->post("/merchant/points/approve/$tx")->assertNotFound();
        $this->assertSame(0.0, $this->balance($fc, $this->other));
    }

    // ---- QR scan claims ----------------------------------------------------------------------------------------

    private function scanningCustomer(): \App\Models\User
    {
        $user = $this->makeUser('customer', null, ['email' => 'ali@example.test']);   // users.email links to customers.email

        return $user;
    }

    public function test_scan_earn_creates_a_pending_claim_and_never_credits_directly(): void
    {
        $user = $this->scanningCustomer();
        $this->setRates($this->m, ['earn_rate' => 100]);

        $this->actingAs($user)->postJson('/api/scan/earn', ['merchant_id' => $this->m, 'amount' => 25])
            ->assertOk()->assertJson(['success' => true, 'pending' => true, 'points_requested' => 25]);

        $this->assertSame(300.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', [
            'customer_id' => $this->c, 'type' => 'earn', 'points' => 25, 'status' => 'pending_approval', 'amount_spent' => 25,
        ]);
    }

    public function test_scan_earn_requires_membership_login_and_a_valid_amount(): void
    {
        $user = $this->scanningCustomer();

        $this->postJson('/api/scan/earn', ['merchant_id' => $this->m, 'amount' => 25])->assertUnauthorized();
        $this->actingAs($user)->postJson('/api/scan/earn', ['merchant_id' => $this->other, 'amount' => 25])->assertStatus(403);
        $this->actingAs($user)->postJson('/api/scan/earn', ['merchant_id' => $this->m, 'amount' => 0])->assertStatus(422);
        $this->assertSame(0, DB::table('points_transactions')->count());
    }

    public function test_scan_earn_is_rate_limited(): void
    {
        $user = $this->scanningCustomer();

        foreach (range(1, 10) as $_) {
            $this->actingAs($user)->postJson('/api/scan/earn', ['merchant_id' => $this->m, 'amount' => 1]);
        }

        $this->actingAs($user)->postJson('/api/scan/earn', ['merchant_id' => $this->m, 'amount' => 1])->assertStatus(429);
    }

    // ---- lapsed subscription = read-only -----------------------------------------------------------------------

    private function lapseSubscription(): void
    {
        $pkg = DB::table('packages')->insertGetId(['name' => 'Pro', 'price' => 99, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('merchants')->where('id', $this->m)->update(['package_id' => $pkg, 'subscription_expiry' => now()->subDays(8)]);
    }

    public function test_lapsed_paid_merchant_cannot_write_but_can_still_approve_points(): void
    {
        $this->lapseSubscription();
        $reward = $this->makeReward($this->m);
        $r = $this->makeRedemption($this->c, $this->m, $reward);
        $tx = $this->makeTx($this->c, $this->m, 'earn', 50, null, 'pending_approval');

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/complete")->assertSessionHas('error');
        $this->assertDatabaseHas('redemptions', ['id' => $r, 'status' => 'approved']);

        $this->actingAs($this->owner)->post("/merchant/points/approve/$tx")->assertSessionHas('success');
    }

    public function test_lapsed_merchant_can_still_read(): void
    {
        $this->lapseSubscription();

        $this->actingAs($this->owner)->get('/merchant/redemptions')->assertOk();
    }

    public function test_grace_period_of_seven_days_keeps_the_merchant_writable(): void
    {
        $pkg = DB::table('packages')->insertGetId(['name' => 'Pro', 'price' => 99, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('merchants')->where('id', $this->m)->update(['package_id' => $pkg, 'subscription_expiry' => now()->subDays(6)]);
        $r = $this->makeRedemption($this->c, $this->m, $this->makeReward($this->m));

        $this->actingAs($this->owner)->post("/merchant/redemptions/$r/complete")->assertSessionHas('success');
    }

    // ---- private verification documents ------------------------------------------------------------------------

    public function test_verification_documents_are_served_only_to_superadmin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('verification/ic/front.jpg', 'secret');
        DB::table('merchants')->where('id', $this->m)->update(['ic_image' => 'verification/ic/front.jpg']);
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)->get("/superadmin/merchants/{$this->m}/document/ic")
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($this->owner)->get("/superadmin/merchants/{$this->m}/document/ic")->assertForbidden();
    }

    public function test_document_route_rejects_path_traversal_and_missing_files(): void
    {
        Storage::fake('local');
        $admin = $this->makeUser('superadmin');

        DB::table('merchants')->where('id', $this->m)->update(['ic_image' => 'verification/ic/../../../.env']);
        $this->actingAs($admin)->get("/superadmin/merchants/{$this->m}/document/ic")->assertNotFound();

        DB::table('merchants')->where('id', $this->m)->update(['ic_image' => 'verification/ic/gone.jpg']);
        $this->actingAs($admin)->get("/superadmin/merchants/{$this->m}/document/ic")->assertNotFound();

        $this->actingAs($admin)->get("/superadmin/merchants/{$this->m}/document/passport")->assertNotFound();
    }

    // ---- login lockout -----------------------------------------------------------------------------------------

    public function test_login_is_locked_after_five_failures_even_with_the_right_password(): void
    {
        $email = 'lock@example.test';
        $this->makeUser('merchant', $this->m, ['email' => $email, 'password' => Hash::make('right-pass')]);

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => $email, 'password' => 'right-pass'])->assertSessionHasErrors('email');
        $this->assertGuest();

        RateLimiter::clear(strtolower($email) . '|127.0.0.1');
        $this->post('/login', ['email' => $email, 'password' => 'right-pass'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_deactivated_staff_cannot_log_in(): void
    {
        $this->makeUser('staff', $this->m, ['email' => 'off@example.test', 'password' => Hash::make('pw-123456'), 'status' => 'inactive']);

        $this->post('/login', ['email' => 'off@example.test', 'password' => 'pw-123456'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
