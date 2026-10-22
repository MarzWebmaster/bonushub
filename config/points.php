<?php

/**
 * Points expiry (PRD v1.2 4.2.2, D13): points lapse after N months WITHOUT ACTIVITY (an approved earn or redeem).
 * Ships switched OFF: expiring customers' points is a legal/consumer-notice matter (PRD flag ⚖️). Turn it on with
 * POINTS_EXPIRY_ENABLED=true in .env only after the terms shown to customers say so.
 */
return [
    'expiry_enabled' => (bool) env('POINTS_EXPIRY_ENABLED', false),

    // default for every merchant; loyalty_rates.points_expiry_months overrides it (0 = this merchant never expires points)
    'default_months' => (int) env('POINTS_EXPIRY_MONTHS', 12),

    // the customer is warned this many days before the points lapse
    'warn_days' => (int) env('POINTS_EXPIRY_WARN_DAYS', 30),

    // range a merchant may choose for its own expiry period (0 = never expire is always allowed)
    'min_months' => (int) env('POINTS_EXPIRY_MIN_MONTHS', 6),
    'max_months' => (int) env('POINTS_EXPIRY_MAX_MONTHS', 36),
];
