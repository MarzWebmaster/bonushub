<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referral_code',
        'customer_id',
        'merchant_id',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'total_clicks',
        'total_signups',
        'total_conversions',
        'points_earned',
        'status',
    ];

    protected $casts = [
        'total_clicks' => 'integer',
        'total_signups' => 'integer',
        'total_conversions' => 'integer',
        'points_earned' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function referredCustomers()
    {
        return $this->hasMany(Customer::class, 'referred_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Generate unique referral code
    public static function generateCode(int $customerId, int $merchantId): string
    {
        return strtoupper(md5("ref-{$customerId}-{$merchantId}-" . now()->timestamp));
    }

    // Track a click
    public function trackClick(): void
    {
        $this->increment('total_clicks');
    }

    // Track a signup
    public function trackSignup(): void
    {
        $this->increment('total_signups');
    }

    // Track conversion (purchase)
    public function trackConversion(int $points): void
    {
        $this->increment('total_conversions');
        $this->increment('points_earned', $points);
    }

    public function getConversionRate(): float
    {
        return $this->total_clicks > 0
            ? round(($this->total_signups / $this->total_clicks) * 100, 1)
            : 0;
    }
}
