<?php

/**
 * Customer referral rules (PRD v1.2 section 5.3.6). The BONUS AMOUNTS are set per merchant on the
 * Loyalty Rates page (0 = disabled); these are only the validation rules shared by all merchants.
 */
return [
    // A referral counts only after the referred customer has been registered at least this many days...
    'min_days' => (int) env('REFERRAL_MIN_DAYS', 3),

    // ...AND has done something (approved points earn, a task submission or a giveaway entry).
    // A signup that is still inactive after this many days is marked invalid ('not_active').
    'max_days' => (int) env('REFERRAL_MAX_DAYS', 30),

    // Maximum valid referrals credited per referrer per calendar month (excess waits for next month).
    'monthly_cap' => (int) env('REFERRAL_MONTHLY_CAP', 20),

    // Fraud flags (merchant reviews flagged signups; they are never credited automatically):
    // a signup is flagged 'duplicate_ip' when this many earlier signups through the SAME link came from the same
    // network (hashed IP) in the last 7 days, and 'velocity' when this many signups came through the link in the last hour.
    'max_per_ip'   => (int) env('REFERRAL_MAX_PER_IP', 2),
    'max_per_hour' => (int) env('REFERRAL_MAX_PER_HOUR', 5),
];
