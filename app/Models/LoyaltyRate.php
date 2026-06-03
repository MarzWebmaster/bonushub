<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRate extends Model
{
    protected $fillable = [
        'merchant_id',
        'rate_per_rm',
        'earn_rate',
        'redeem_rate',
        'min_redeem',
        'max_redeem',
        'festive_multiplier',
        'product_specific_rules',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_rm' => 'decimal:2',
            'festive_multiplier' => 'json',
            'product_specific_rules' => 'json',
        ];
    }

    /**
     * Get the merchant that owns the loyalty rate.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
