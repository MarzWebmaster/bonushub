<?php

namespace Tests\Feature;

use App\Http\Controllers\CampaignRedirectController;
use App\Http\Controllers\ViralTaskController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MakesLoyaltyData;
use Tests\TestCase;

/** Duplicate task-proof detection and the tracked campaign link (/r/{slug}) with bot filtering and unique visitors. */
class ViralAndCampaignTest extends TestCase
{
    use RefreshDatabase, MakesLoyaltyData;

    private int $m;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->m = $this->makeMerchant();
    }

    // ---- proof URL normalisation -------------------------------------------------------------------------------

    #[DataProvider('equivalentUrls')]
    public function test_equivalent_proof_urls_normalise_to_the_same_string(string $a, string $b): void
    {
        $this->assertSame(ViralTaskController::normaliseProofUrl($a), ViralTaskController::normaliseProofUrl($b));
    }

    public static function equivalentUrls(): array
    {
        return [
            'host case'        => ['https://WWW.Facebook.com/p/1', 'https://www.facebook.com/p/1'],
            'trailing slash'   => ['https://x.com/a/b/', 'https://x.com/a/b'],
            'fragment'         => ['https://x.com/a#top', 'https://x.com/a'],
            'tracking params'  => ['https://x.com/a?utm_source=wa&fbclid=1&gclid=2', 'https://x.com/a'],
            'query order'      => ['https://x.com/a?b=2&a=1', 'https://x.com/a?a=1&b=2'],
            'surrounding space' => ['  https://x.com/a  ', 'https://x.com/a'],
        ];
    }

    public function test_different_proof_urls_stay_different(): void
    {
        $this->assertNotSame(
            ViralTaskController::normaliseProofUrl('https://x.com/a?id=1'),
            ViralTaskController::normaliseProofUrl('https://x.com/a?id=2')
        );
    }

    // ---- task submissions --------------------------------------------------------------------------------------

    private function customerUser(string $name): array
    {
        $cust = $this->makeCustomer($name);
        $this->joinMerchant($cust, $this->m);
        $user = $this->makeUser('customer', null, ['email' => strtolower($name) . '@example.test']);

        return [$cust, $user];
    }

    private function task(string $title = 'Share post'): int
    {
        return $this->fillRow('viral_tasks', ['merchant_id' => $this->m, 'title' => $title, 'status' => 'active', 'requires_screenshot' => 0]);
    }

    private function submit($user, int $taskId, array $body = [])
    {
        return $this->actingAs($user)->postJson("/customer/tasks/$taskId/submit", $body);
    }

    public function test_submission_stores_hashes_and_is_pending(): void
    {
        [, $user] = $this->customerUser('Ali');
        $t = $this->task();

        $this->submit($user, $t, ['proof_url' => 'https://x.com/p/1?utm_source=wa'])->assertCreated();

        $row = DB::table('task_submissions')->first();
        $this->assertSame('pending', $row->status);
        $this->assertSame(hash('sha256', 'https://x.com/p/1'), $row->proof_url_hash);
        $this->assertNull($row->duplicate_of_id);
    }

    public function test_same_proof_from_another_customer_is_accepted_but_flagged(): void
    {
        [, $ali] = $this->customerUser('Ali');
        [, $budi] = $this->customerUser('Budi');
        $t = $this->task();

        $this->submit($ali, $t, ['proof_url' => 'https://x.com/p/1'])->assertCreated();
        $this->submit($budi, $t, ['proof_url' => 'https://X.com/p/1/?utm_campaign=z'])->assertCreated();

        $first = DB::table('task_submissions')->orderBy('id')->first();
        $second = DB::table('task_submissions')->orderByDesc('id')->first();
        $this->assertSame($first->id, $second->duplicate_of_id);
    }

    public function test_same_customer_cannot_reuse_a_proof_on_another_task(): void
    {
        [, $ali] = $this->customerUser('Ali');
        $t1 = $this->task('One');
        $t2 = $this->task('Two');

        $this->submit($ali, $t1, ['proof_url' => 'https://x.com/p/1'])->assertCreated();
        $this->submit($ali, $t2, ['proof_url' => 'https://x.com/p/1'])->assertStatus(422);

        $this->assertSame(1, DB::table('task_submissions')->count());
    }

    public function test_same_screenshot_is_detected_by_content_hash(): void
    {
        [, $ali] = $this->customerUser('Ali');
        [, $budi] = $this->customerUser('Budi');
        $t = $this->task();
        $png = fn (string $name) => UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        $this->submit($ali, $t, ['screenshot' => $png('a.png')])->assertCreated();
        $this->submit($budi, $t, ['screenshot' => $png('different-name.png')])->assertCreated();

        $this->assertNotNull(DB::table('task_submissions')->orderByDesc('id')->value('duplicate_of_id'));
    }

    public function test_rejected_submissions_do_not_count_as_the_original(): void
    {
        [, $ali] = $this->customerUser('Ali');
        [, $budi] = $this->customerUser('Budi');
        $t = $this->task();
        $this->submit($ali, $t, ['proof_url' => 'https://x.com/p/1'])->assertCreated();
        DB::table('task_submissions')->update(['status' => 'rejected']);

        $this->submit($budi, $t, ['proof_url' => 'https://x.com/p/1'])->assertCreated();

        $this->assertNull(DB::table('task_submissions')->orderByDesc('id')->value('duplicate_of_id'));
    }

    public function test_a_customer_can_submit_only_once_per_task_and_five_times_a_day(): void
    {
        [, $ali] = $this->customerUser('Ali');
        $t = $this->task();
        $this->submit($ali, $t, ['proof_url' => 'https://x.com/p/0'])->assertCreated();
        $this->submit($ali, $t, ['proof_url' => 'https://x.com/p/00'])->assertStatus(422);

        foreach (range(1, 4) as $i) {
            $this->submit($ali, $this->task("T$i"), ['proof_url' => "https://x.com/p/n$i"])->assertCreated();
        }
        $this->submit($ali, $this->task('T9'), ['proof_url' => 'https://x.com/p/n9'])->assertStatus(429);
    }

    // ---- campaign links ----------------------------------------------------------------------------------------

    private function link(array $o = []): int
    {
        return $this->fillRow('campaign_links', $o + ['merchant_id' => $this->m, 'name' => 'IG Raya', 'slug' => 'ig-raya', 'status' => 'active']);
    }

    private function visit(string $ua, string $ip = '10.0.0.1', string $slug = 'ig-raya')
    {
        return $this->withHeaders(['User-Agent' => $ua])->withServerVariables(['REMOTE_ADDR' => $ip])->get("/r/$slug");
    }

    private const PHONE = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120 Mobile Safari/537.36';

    public function test_visit_redirects_to_register_and_counts_total_and_unique(): void
    {
        $id = $this->link();

        $this->visit(self::PHONE)->assertRedirect('/register?ref=ig-raya');
        $this->visit(self::PHONE)->assertRedirect();                       // same person again
        $this->visit(self::PHONE, '10.0.0.2')->assertRedirect();           // someone else

        $this->assertDatabaseHas('campaign_links', ['id' => $id, 'visits' => 3, 'unique_visits' => 2]);
        $this->assertSame([2, 1], DB::table('campaign_link_visitors')->where('campaign_link_id', $id)->orderByDesc('hits')->pluck('hits')->map(fn ($h) => (int) $h)->all());
    }

    public function test_raw_ip_is_not_stored_for_visitors(): void
    {
        $this->link();
        $this->visit(self::PHONE, '203.0.113.9');

        $hash = DB::table('campaign_link_visitors')->value('visitor_hash');

        $this->assertStringNotContainsString('203.0.113.9', $hash);
        $this->assertSame(64, strlen($hash));
    }

    #[DataProvider('botAgents')]
    public function test_link_preview_bots_are_redirected_but_not_counted(string $ua): void
    {
        $id = $this->link();

        $this->visit($ua)->assertRedirect('/register?ref=ig-raya');

        $this->assertDatabaseHas('campaign_links', ['id' => $id, 'visits' => 0, 'unique_visits' => 0]);
    }

    public static function botAgents(): array
    {
        return [
            'whatsapp'  => ['WhatsApp/2.23.20 A'],
            'facebook'  => ['facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'],
            'telegram'  => ['TelegramBot (like TwitterBot)'],
            'googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'curl'      => ['curl/8.4.0'],
            'headless'  => ['Mozilla/5.0 HeadlessChrome/120'],
            'empty'     => [''],
        ];
    }

    public function test_looks_like_bot_lets_real_browsers_through(): void
    {
        $this->assertFalse(CampaignRedirectController::looksLikeBot(self::PHONE));
        $this->assertTrue(CampaignRedirectController::looksLikeBot('   '));
    }

    public function test_unknown_expired_or_inactive_links_redirect_home_without_counting(): void
    {
        $expired = $this->link(['slug' => 'old', 'expires_at' => now()->subDay()]);
        $paused = $this->link(['slug' => 'paused', 'status' => 'paused']);

        $this->visit(self::PHONE, '10.0.0.1', 'nope')->assertRedirect('/');
        $this->visit(self::PHONE, '10.0.0.1', 'old')->assertRedirect('/');
        $this->visit(self::PHONE, '10.0.0.1', 'paused')->assertRedirect('/');

        $this->assertDatabaseHas('campaign_links', ['id' => $expired, 'visits' => 0]);
        $this->assertDatabaseHas('campaign_links', ['id' => $paused, 'visits' => 0]);
    }
}
