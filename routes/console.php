<?php

use App\Models\GiveawayCampaign;
use App\Services\GiveawayScoring;
use App\Services\PointsExpiry;
use App\Services\ReferralAttribution;
use App\Services\TierService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PRD v1.2 5.3.6: promote/retire pending customer-referral signups (daily; the server's
// `schedule:run` cron entry already fires every minute).
Artisan::command('referrals:validate', function () {
    $s = app(ReferralAttribution::class)->validatePending();
    $this->info(sprintf(
        'Referral validation: %d valid, %d invalid, %d waiting, %d flagged for review',
        $s['validated'], $s['invalidated'], $s['held'], $s['flagged']
    ));
})->purpose('Validate pending customer-referral signups and credit merchant-set bonus points');

Schedule::command('referrals:validate')->dailyAt('03:15')->withoutOverlapping();

// PRD v1.2 4.2.2: points lapse after N months without activity, with a warning first. Ships OFF (config/points.php);
// `--dry` always works and only reports what a real run would do.
Artisan::command('points:expire {--dry : only report}', function () {
    $dry = (bool) $this->option('dry');
    if (!$dry && !config('points.expiry_enabled')) {
        $this->info('Points expiry is disabled (POINTS_EXPIRY_ENABLED=false). Nothing done.');
        return;
    }
    $r = app(PointsExpiry::class)->run($dry);
    $this->info(($dry ? 'Would expire' : 'Expired') . " {$r['expired']} balance(s) / " . number_format($r['points']) . " points; warned {$r['warned']} customer(s)" . ($dry ? ' (dry run)' : ''));
})->purpose('Expire points of customers inactive for the configured period and warn the ones about to lose them');

Schedule::command('points:expire')->dailyAt('03:45')->withoutOverlapping();

// PRD v1.2 7.2: tier = points earned in the rolling 12 months; the daily pass only ever moves customers UP.
Artisan::command('tiers:refresh {--dry : only count the customers who would move up}', function () {
    $n = app(TierService::class)->refreshAll((bool) $this->option('dry'));
    $this->info(($this->option('dry') ? 'Would upgrade' : 'Upgraded') . " {$n} customer(s)");
})->purpose('Upgrade customers to the tier their last-12-month earnings qualify for');

Schedule::command('tiers:refresh')->dailyAt('03:30')->withoutOverlapping();

// PRD v1.2 G1: refresh giveaway referral scores of campaigns that are running or waiting for a draw.
Artisan::command('giveaways:refresh-scores', function () {
    $svc = app(GiveawayScoring::class);
    $n = 0;
    GiveawayCampaign::whereIn('status', ['active', 'ended'])->whereDoesntHave('winners')->each(function ($c) use ($svc, &$n) {
        $n += $svc->refresh($c);
    });
    $this->info("Giveaway scores refreshed: {$n} entries changed");
})->purpose('Recompute referral scores of giveaway entries');

Schedule::command('giveaways:refresh-scores')->hourly()->withoutOverlapping();

// E-mails (points earned, task approved, reward redeemed) are queued; nothing processed the queue before.
// `schedule:run` already fires every minute from cron: drain whatever is waiting, then exit.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3 --sleep=1')->everyMinute()->withoutOverlapping(10);
