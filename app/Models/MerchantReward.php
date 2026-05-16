<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantReward extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'description',
        'points_required',
        'stock_quantity',
        'stock_left',
        'claim_type',
        'delivery_cost',
        'delivery_fee',
        'download_url',
        'access_code_prefix',
        'status',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    /**
     * Get the merchant that owns the reward.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the redemptions for this reward.
     */
    public function redemptions()
    {
        return $this->hasMany(Redemption::class, 'reward_id');
    }
}
