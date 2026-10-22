<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

/** Counter console (StaffController): award, redeem, hand over by claim code, void. */
class StaffCounterTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private int $other;
    private int $c;
    private $owner;
    private $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->m = $this->makeMerchant();
        $this->other = $this->makeMerchant(['company_name' => 'Kedai Lain']);
        $this->c = $this->makeCustomer('Ali');
        $this->joinMerchant($this->c, $this->m, 500);
        $this->owner = $this->makeUser('merchant', $this->m);
        $this->staff = $this->makeUser('staff', $this->m);
    }

    // ---- access ------------------------------------------------------------------------------------------------

    public function test_guests_are_redirected_to_login(): void
    {
        $this->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 10])->assertUnauthorized();
    }

    public function test_a_customer_account_cannot_use_the_counter(): void
    {
        $customer = $this->makeUser('customer', $this->m);

        $this->actingAs($customer)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 10])->assertForbidden();
    }

    public function test_deactivated_staff_are_locked_out(): void
    {
        $inactive = $this->makeUser('staff', $this->m, ['status' => 'inactive']);

        $this->actingAs($inactive)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 10])->assertForbidden();
    }

    public function test_staff_without_a_merchant_are_locked_out(): void
    {
        $orphan = $this->makeUser('staff', null);

        $this->actingAs($orphan)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 10])->assertForbidden();
    }

    // ---- add points --------------------------------------------------------------------------------------------

    public function test_staff_award_below_the_threshold_is_credited_immediately(): void
    {
        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 200])
            ->assertOk()
            ->assertJson(['success' => true, 'needs_approval' => false, 'new_balance' => 700]);

        $this->assertSame(700.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', [
            'customer_id' => $this->c, 'type' => 'earn', 'points' => 200, 'status' => 'approved', 'staff_id' => $this->staff->id,
        ]);
    }

    public function test_staff_award_of_1000_or_more_waits_for_approval(): void
    {
        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 1000])
            ->assertOk()
            ->assertJson(['needs_approval' => true, 'new_balance' => 500]);

        $this->assertSame(500.0, $this->balance($this->c, $this->m), 'balance must not move until the merchant approves');
        $this->assertDatabaseHas('points_transactions', ['type' => 'earn', 'points' => 1000, 'status' => 'pending_approval', 'approved_by' => null]);
    }

    public function test_the_owner_is_not_subject_to_the_approval_threshold(): void
    {
        $this->actingAs($this->owner)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 4000])
            ->assertOk()->assertJson(['needs_approval' => false]);

        $this->assertSame(4500.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', ['points' => 4000, 'status' => 'approved', 'approved_by' => $this->owner->id]);
    }

    public function test_staff_daily_limit_sends_further_awards_to_approval(): void
    {
        // 5 x 999 = 4995 stays within the 5000 limit; the 6th would push the 24h total past it
        foreach (range(1, 6) as $i) {
            $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 999])
                ->assertJson(['needs_approval' => $i === 6]);
        }
    }

    public function test_multiplier_promo_is_applied_before_the_threshold_check(): void
    {
        DB::table('promos')->insert($this->promoRow(['merchant_id' => $this->m, 'type' => 'multiplier', 'value' => 3]));

        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 400])
            ->assertOk()
            ->assertJson(['points' => 1200, 'needs_approval' => true]);
    }

    public function test_cannot_award_points_to_someone_elses_customer(): void
    {
        $stranger = $this->makeCustomer('Stranger');
        $this->joinMerchant($stranger, $this->other, 0);

        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $stranger, 'points' => 10])->assertNotFound();
        $this->assertDatabaseMissing('points_transactions', ['customer_id' => $stranger]);
    }

    public function test_award_validation(): void
    {
        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 0])->assertStatus(422);
        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => 9999, 'points' => 5])->assertStatus(422);
        $this->actingAs($this->staff)->postJson('/staff/add-points', ['customer_id' => $this->c, 'points' => 100001])->assertStatus(422);
    }

    // ---- redeem ------------------------------------------------------------------------------------------------

    public function test_redeem_deducts_points_and_stock_and_issues_a_claim_code(): void
    {
        $reward = $this->makeReward($this->m, 100, 5);

        $res = $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $reward, 'quantity' => 2])
            ->assertOk()->assertJson(['success' => true, 'points_deducted' => 200, 'new_balance' => 300]);

        $this->assertSame(300.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('merchant_rewards', ['id' => $reward, 'stock_left' => 3]);
        $this->assertDatabaseHas('points_transactions', ['type' => 'redeem', 'points' => -200, 'status' => 'approved']);
        $this->assertDatabaseHas('redemptions', [
            'customer_id' => $this->c, 'reward_id' => $reward, 'claim_code' => $res->json('claim_code'),
            'quantity' => 2, 'status' => 'completed',   // self_collect is handed over on the spot
            'points_transaction_id' => $res->json('transaction_id'),
        ]);
    }

    public function test_redeem_of_a_non_self_collect_reward_waits_for_hand_over(): void
    {
        $reward = $this->makeReward($this->m, 100, 5, ['claim_type' => 'delivery']);

        $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $reward])->assertOk();

        $this->assertDatabaseHas('redemptions', ['reward_id' => $reward, 'status' => 'approved']);
    }

    public function test_redeem_fails_when_points_are_insufficient(): void
    {
        $reward = $this->makeReward($this->m, 600, 5);

        $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $reward])->assertStatus(422);

        $this->assertSame(500.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseMissing('redemptions', ['reward_id' => $reward]);
    }

    public function test_redeem_fails_when_stock_is_insufficient(): void
    {
        $reward = $this->makeReward($this->m, 10, 1);

        $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $reward, 'quantity' => 2])->assertStatus(422);

        $this->assertSame(500.0, $this->balance($this->c, $this->m));
    }

    public function test_cannot_redeem_another_merchants_reward_or_an_inactive_one(): void
    {
        $foreign = $this->makeReward($this->other, 10, 5);
        $inactive = $this->makeReward($this->m, 10, 5, ['status' => 'inactive']);

        $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $foreign])->assertNotFound();
        $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $inactive])->assertNotFound();
        $this->assertSame(500.0, $this->balance($this->c, $this->m));
    }

    // ---- claim code --------------------------------------------------------------------------------------------

    public function test_claim_code_hands_over_once_only(): void
    {
        $reward = $this->makeReward($this->m);
        $this->makeRedemption($this->c, $this->m, $reward, ['claim_code' => 'ABCD1234']);

        $this->actingAs($this->staff)->postJson('/staff/claim', ['code' => ' abcd1234 '])->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('redemptions', ['claim_code' => 'ABCD1234', 'status' => 'completed', 'staff_id' => $this->staff->id]);

        $this->actingAs($this->staff)->postJson('/staff/claim', ['code' => 'ABCD1234'])->assertStatus(422);
    }

    public function test_claim_code_of_another_merchant_or_a_cancelled_redemption_is_refused(): void
    {
        $foreignCustomer = $this->makeCustomer('Other');
        $this->joinMerchant($foreignCustomer, $this->other);
        $foreignReward = $this->makeReward($this->other);
        $this->makeRedemption($foreignCustomer, $this->other, $foreignReward, ['claim_code' => 'FOREIGN1']);
        $reward = $this->makeReward($this->m);
        $this->makeRedemption($this->c, $this->m, $reward, ['claim_code' => 'CANCEL01', 'status' => 'cancelled']);

        $this->actingAs($this->staff)->postJson('/staff/claim', ['code' => 'FOREIGN1'])->assertNotFound();
        $this->actingAs($this->staff)->postJson('/staff/claim', ['code' => 'CANCEL01'])->assertStatus(422);
        $this->assertDatabaseHas('redemptions', ['claim_code' => 'FOREIGN1', 'status' => 'approved']);
    }

    // ---- void --------------------------------------------------------------------------------------------------

    public function test_voiding_an_earn_reverses_the_points_and_cannot_be_repeated(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 200);

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $tx, 'reason' => 'Salah entri'])
            ->assertOk()->assertJson(['new_balance' => 300]);

        $this->assertSame(300.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('points_transactions', ['type' => 'void', 'points' => -200, 'void_of_id' => $tx, 'status' => 'approved']);

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $tx, 'reason' => 'Sekali lagi'])->assertStatus(422);
        $this->assertSame(300.0, $this->balance($this->c, $this->m), 'a second void must not move the balance again');
    }

    public function test_voiding_a_redeem_cancels_the_redemption_and_restores_points_and_stock(): void
    {
        $reward = $this->makeReward($this->m, 100, 5);
        $res = $this->actingAs($this->staff)->postJson('/staff/redeem', ['customer_id' => $this->c, 'reward_id' => $reward, 'quantity' => 2]);
        $txId = $res->json('transaction_id');

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $txId, 'reason' => 'Pelanggan batal'])->assertOk();

        $this->assertSame(500.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseHas('merchant_rewards', ['id' => $reward, 'stock_left' => 5]);
        $this->assertDatabaseHas('redemptions', ['points_transaction_id' => $txId, 'status' => 'cancelled']);
    }

    public function test_pending_and_foreign_transactions_cannot_be_voided(): void
    {
        $pending = $this->makeTx($this->c, $this->m, 'earn', 1000, null, 'pending_approval');
        $stranger = $this->makeCustomer('Stranger');
        $this->joinMerchant($stranger, $this->other, 100);
        $foreign = $this->makeTx($stranger, $this->other, 'earn', 100);

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $pending, 'reason' => 'test'])->assertStatus(422);
        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $foreign, 'reason' => 'test'])->assertNotFound();
        $this->assertSame(100.0, $this->balance($stranger, $this->other));
    }

    public function test_staff_can_only_void_within_48_hours_but_the_owner_can_always(): void
    {
        $old = $this->makeTx($this->c, $this->m, 'earn', 100, now()->subHours(49));

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $old, 'reason' => 'terlambat'])->assertForbidden();
        $this->assertSame(500.0, $this->balance($this->c, $this->m));

        $this->actingAs($this->owner)->postJson('/staff/void', ['transaction_id' => $old, 'reason' => 'owner override'])->assertOk();
        $this->assertSame(400.0, $this->balance($this->c, $this->m));
    }

    public function test_void_is_refused_when_it_would_make_the_balance_negative(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 800);   // customer already spent most of it: balance is 500

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $tx, 'reason' => 'test'])->assertStatus(422);

        $this->assertSame(500.0, $this->balance($this->c, $this->m));
        $this->assertDatabaseMissing('points_transactions', ['type' => 'void']);
    }

    public function test_void_requires_a_reason(): void
    {
        $tx = $this->makeTx($this->c, $this->m, 'earn', 10);

        $this->actingAs($this->staff)->postJson('/staff/void', ['transaction_id' => $tx])->assertStatus(422);
    }

    private function promoRow(array $o): array
    {
        $row = $o + ['name' => 'Promo', 'status' => 'active', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'created_at' => now(), 'updated_at' => now()];
        foreach (DB::getSchemaBuilder()->getColumns('promos') as $c) {
            if (!$c['nullable'] && $c['default'] === null && !$c['auto_increment'] && !array_key_exists($c['name'], $row)) {
                $row[$c['name']] = str_contains($c['type'], 'int') ? 1 : 'x';
            }
        }

        return $row;
    }
}
