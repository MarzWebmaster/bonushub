<?php

namespace Tests\Feature;

use App\Mail\GiveawayWinnerMail;
use App\Models\GiveawayCampaign;
use App\Services\GiveawayScoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

/** Referral-scored giveaways: scoring, draws (top referrers / threshold / milestone / manual), claim and replacement. */
class GiveawayTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;
    private $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
        $this->m = $this->makeMerchant();
        $this->owner = $this->makeUser('merchant', $this->m);
    }

    private function campaign(array $o = []): int
    {
        return $this->fillRow('giveaway_campaigns', $o + [
            'merchant_id'          => $this->m,
            'title'                => 'Raya Giveaway',
            'prize_description'    => 'Hamper',
            'status'               => 'ended',
            'selection_method'     => 'top_referrers',
            'winner_count'         => 2,
            'entries_per_referral' => 3,
            'starts_at'            => now()->subDays(30),
            'ends_at'              => now()->subDay(),
            'terms'                => 'T&C',
        ]);
    }

    /** an entrant (customer + entry) who has brought in $valid valid signups through their referral link */
    private function entrant(int $campaignId, string $name, int $valid = 0, array $entry = []): array
    {
        $cust = $this->makeCustomer($name);
        $this->joinMerchant($cust, $this->m);
        $entryId = $this->fillRow('giveaway_entries', $entry + ['giveaway_campaign_id' => $campaignId, 'customer_id' => $cust]);
        $this->referralSignups($cust, $valid);

        return [$cust, $entryId];
    }

    private function referralSignups(int $referrerId, int $n, string $status = 'valid', $at = null): void
    {
        if ($n < 1) {
            return;
        }
        $ref = DB::table('referrals')->where('customer_id', $referrerId)->where('merchant_id', $this->m)->value('id')
            ?? DB::table('referrals')->insertGetId([
                'referral_code' => 'R' . $referrerId, 'customer_id' => $referrerId, 'merchant_id' => $this->m,
                'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ]);
        foreach (range(1, $n) as $_) {
            $referred = $this->makeCustomer('ref' . uniqid());
            DB::table('referral_signups')->insert([
                'referral_id' => $ref, 'referred_customer_id' => $referred, 'status' => $status,
                'created_at' => $at ?? now()->subDays(10), 'updated_at' => now(),
            ]);
        }
    }

    private function draw(int $campaignId, array $body = [])
    {
        return $this->actingAs($this->owner)->postJson("/merchant/giveaways/$campaignId/select-winners", $body);
    }

    // ---- scoring -----------------------------------------------------------------------------------------------

    public function test_score_counts_only_valid_signups_inside_the_campaign_window(): void
    {
        $c = $this->campaign();
        [$cust, $entry] = $this->entrant($c, 'Ali', 2);                       // 2 valid, inside the window
        $this->referralSignups($cust, 1, 'pending');                           // pending: not counted
        $this->referralSignups($cust, 1, 'invalid');                           // invalid: not counted
        $this->referralSignups($cust, 1, 'valid', now()->subDays(40));         // before the window
        $this->referralSignups($cust, 1, 'valid', now()->subHours(1));         // after ends_at (yesterday)

        $changed = app(GiveawayScoring::class)->refresh(GiveawayCampaign::find($c));

        $this->assertSame(1, $changed);
        $this->assertDatabaseHas('giveaway_entries', ['id' => $entry, 'score' => 2, 'entry_count' => 1 + 2 * 3]);
    }

    public function test_score_is_scoped_to_the_campaigns_merchant(): void
    {
        $c = $this->campaign();
        [$cust, $entry] = $this->entrant($c, 'Ali', 1);
        $otherMerchant = $this->makeMerchant(['company_name' => 'Lain']);
        $ref = DB::table('referrals')->insertGetId([
            'referral_code' => 'OTHER', 'customer_id' => $cust, 'merchant_id' => $otherMerchant,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('referral_signups')->insert([
            'referral_id' => $ref, 'referred_customer_id' => $this->makeCustomer('x'), 'status' => 'valid',
            'created_at' => now()->subDays(5), 'updated_at' => now(),
        ]);

        app(GiveawayScoring::class)->refresh(GiveawayCampaign::find($c));

        $this->assertDatabaseHas('giveaway_entries', ['id' => $entry, 'score' => 1]);
    }

    public function test_legacy_campaigns_without_terms_keep_their_entry_counts(): void
    {
        $c = $this->campaign(['terms' => null]);
        [, $entry] = $this->entrant($c, 'Ali', 2, ['entry_count' => 9]);

        app(GiveawayScoring::class)->refresh(GiveawayCampaign::find($c));

        $this->assertDatabaseHas('giveaway_entries', ['id' => $entry, 'score' => 2, 'entry_count' => 9]);
    }

    // ---- draws -------------------------------------------------------------------------------------------------

    public function test_top_referrers_draw_picks_the_highest_scores_up_to_winner_count(): void
    {
        $c = $this->campaign(['winner_count' => 2]);
        [$first] = $this->entrant($c, 'First', 5);
        [$second] = $this->entrant($c, 'Second', 3);
        $this->entrant($c, 'Third', 1);
        $this->entrant($c, 'NoScore', 0);

        $res = $this->draw($c)->assertOk()->assertJson(['success' => true]);

        $this->assertSame([$first, $second], collect($res->json('winners'))->pluck('customer_id')->all());
        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $first, 'position' => 1, 'status' => 'pending']);
        $this->assertDatabaseHas('giveaway_campaigns', ['id' => $c, 'status' => 'ended']);
        $this->assertDatabaseHas('giveaway_entries', ['customer_id' => $first, 'is_winner' => 1, 'prize_won' => 'Hamper']);
        Mail::assertQueued(GiveawayWinnerMail::class, 2);
    }

    public function test_equal_scores_are_broken_by_earliest_entry(): void
    {
        $c = $this->campaign(['winner_count' => 1]);
        $this->entrant($c, 'Late', 2, ['created_at' => now()->subHour()]);
        [$early] = $this->entrant($c, 'Early', 2, ['created_at' => now()->subDays(3)]);

        $this->draw($c)->assertOk();

        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $early]);
    }

    public function test_threshold_draw_only_takes_entrants_who_reached_the_target(): void
    {
        $c = $this->campaign(['selection_method' => 'threshold', 'threshold_score' => 3, 'winner_count' => 5]);
        [$yes] = $this->entrant($c, 'Yes', 3);
        $this->entrant($c, 'Almost', 2);

        $res = $this->draw($c)->assertOk();

        $this->assertSame([$yes], collect($res->json('winners'))->pluck('customer_id')->all());
    }

    public function test_threshold_without_a_target_is_refused(): void
    {
        $c = $this->campaign(['selection_method' => 'threshold', 'threshold_score' => null]);
        $this->entrant($c, 'Ali', 9);

        $this->draw($c)->assertStatus(422);
        $this->assertSame(0, DB::table('giveaway_winners')->count());
    }

    public function test_milestone_draw_awards_the_prize_of_the_highest_milestone_reached(): void
    {
        $c = $this->campaign([
            'selection_method' => 'milestone', 'winner_count' => 5,
            'milestones' => json_encode([['score' => 2, 'prize' => 'Baucar'], ['score' => 5, 'prize' => 'Hamper Besar']]),
        ]);
        [$big] = $this->entrant($c, 'Big', 6);
        [$small] = $this->entrant($c, 'Small', 2);
        $this->entrant($c, 'None', 1);

        $this->draw($c)->assertOk();

        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $big, 'prize_description' => 'Hamper Besar']);
        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $small, 'prize_description' => 'Baucar']);
        $this->assertSame(2, DB::table('giveaway_winners')->count());
    }

    public function test_manual_draw_requires_winners_and_a_reason_of_ten_characters(): void
    {
        $c = $this->campaign(['selection_method' => 'manual']);
        [$cust] = $this->entrant($c, 'Ali');

        $this->draw($c)->assertStatus(422);
        $this->draw($c, ['winner_ids' => [$cust], 'reason' => 'pendek'])->assertStatus(422);
        $this->draw($c, ['winner_ids' => [9999], 'reason' => 'Dipilih oleh pemilik kedai'])->assertStatus(422);
        $this->assertSame(0, DB::table('giveaway_winners')->count());

        $this->draw($c, ['winner_ids' => [$cust], 'reason' => 'Dipilih oleh pemilik kedai'])->assertOk();
        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $cust, 'notes' => 'Manual: Dipilih oleh pemilik kedai']);
    }

    public function test_manual_draw_cannot_exceed_winner_count(): void
    {
        $c = $this->campaign(['selection_method' => 'manual', 'winner_count' => 1]);
        [$a] = $this->entrant($c, 'A');
        [$b] = $this->entrant($c, 'B');

        $this->draw($c, ['winner_ids' => [$a, $b], 'reason' => 'Dua orang pemenang'])->assertStatus(422);
        $this->assertSame(0, DB::table('giveaway_winners')->count());
    }

    public function test_cannot_draw_before_the_campaign_has_ended_or_twice(): void
    {
        $running = $this->campaign(['status' => 'active', 'ends_at' => now()->addDay()]);
        $this->entrant($running, 'Ali', 3);
        $this->draw($running)->assertStatus(422);

        $ended = $this->campaign();
        $this->entrant($ended, 'Budi', 3);
        $this->draw($ended)->assertOk();
        $this->draw($ended)->assertStatus(422);
        $this->assertSame(1, DB::table('giveaway_winners')->where('giveaway_campaign_id', $ended)->count());
    }

    public function test_an_active_campaign_past_its_end_time_can_be_drawn(): void
    {
        $c = $this->campaign(['status' => 'active', 'ends_at' => now()->subMinute()]);
        $this->entrant($c, 'Ali', 2);

        $this->draw($c)->assertOk();
    }

    public function test_draw_with_nobody_eligible_is_refused(): void
    {
        $c = $this->campaign();
        $this->entrant($c, 'Ali', 0);

        $this->draw($c)->assertStatus(422);
    }

    public function test_a_posted_method_must_match_the_campaigns_method(): void
    {
        $c = $this->campaign();
        $this->entrant($c, 'Ali', 2);

        $this->draw($c, ['method' => 'threshold'])->assertStatus(422);
    }

    public function test_a_merchant_cannot_draw_another_merchants_campaign(): void
    {
        $other = $this->makeMerchant(['company_name' => 'Lain']);
        $c = $this->campaign(['merchant_id' => $other]);

        $this->draw($c)->assertNotFound();
    }

    // ---- claim and replacement ---------------------------------------------------------------------------------

    private function drawnCampaign(): array
    {
        $c = $this->campaign(['winner_count' => 1]);
        [$a] = $this->entrant($c, 'Alpha', 5);
        [$b] = $this->entrant($c, 'Bravo', 4);
        [$d] = $this->entrant($c, 'Delta', 2);
        $this->draw($c)->assertOk();

        return [$c, $a, $b, $d, (int) DB::table('giveaway_winners')->where('customer_id', $a)->value('id')];
    }

    public function test_claim_marks_a_pending_winner_once(): void
    {
        [$c, , , , $w] = $this->drawnCampaign();

        $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/claim")->assertOk();
        $this->assertDatabaseHas('giveaway_winners', ['id' => $w, 'status' => 'claimed']);

        $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/claim")->assertStatus(422);
    }

    public function test_replacement_takes_the_next_ranked_entrant_and_keeps_the_position(): void
    {
        [$c, $a, $b, , $w] = $this->drawnCampaign();

        $res = $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/replace")->assertOk();

        $this->assertDatabaseHas('giveaway_winners', ['id' => $w, 'status' => 'forfeited']);
        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $b, 'position' => 1, 'status' => 'pending', 'replaces_winner_id' => $w]);
        $this->assertDatabaseHas('giveaway_entries', ['customer_id' => $a, 'is_winner' => 0]);
        $this->assertDatabaseHas('giveaway_entries', ['customer_id' => $b, 'is_winner' => 1]);
        $this->assertSame($b, $res->json('winner.customer_id'));
        Mail::assertQueued(GiveawayWinnerMail::class, 2);   // original winner + replacement
    }

    public function test_a_claimed_winner_cannot_be_replaced(): void
    {
        [$c, , , , $w] = $this->drawnCampaign();
        $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/claim");

        $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/replace")->assertStatus(422);
    }

    public function test_replacement_never_picks_someone_who_already_won(): void
    {
        $c = $this->campaign(['winner_count' => 1]);
        [$a] = $this->entrant($c, 'Alpha', 5);
        $this->draw($c)->assertOk();
        $w = (int) DB::table('giveaway_winners')->where('customer_id', $a)->value('id');

        // the only entrant already won (and is the forfeiting winner), so nobody is left
        $this->actingAs($this->owner)->postJson("/merchant/giveaways/$c/winners/$w/replace")->assertStatus(422);
        $this->assertDatabaseHas('giveaway_winners', ['id' => $w, 'status' => 'pending']);
    }

    public function test_manual_replacement_needs_a_customer_and_a_reason(): void
    {
        $c = $this->campaign(['selection_method' => 'manual', 'winner_count' => 1]);
        [$a] = $this->entrant($c, 'Alpha');
        [$b] = $this->entrant($c, 'Bravo');
        $this->draw($c, ['winner_ids' => [$a], 'reason' => 'Pilihan pertama pemilik'])->assertOk();
        $w = (int) DB::table('giveaway_winners')->where('customer_id', $a)->value('id');
        $url = "/merchant/giveaways/$c/winners/$w/replace";

        $this->actingAs($this->owner)->postJson($url)->assertStatus(422);
        $this->actingAs($this->owner)->postJson($url, ['customer_id' => $a, 'reason' => 'Orang yang sama lagi'])->assertStatus(422);
        $this->actingAs($this->owner)->postJson($url, ['customer_id' => $b, 'reason' => 'Pemenang asal tidak hadir'])->assertOk();

        $this->assertDatabaseHas('giveaway_winners', ['customer_id' => $b, 'notes' => 'Manual replacement: Pemenang asal tidak hadir']);
    }
}
