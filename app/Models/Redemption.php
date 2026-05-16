<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    protected $fillable = [
        'customer_id',
        'merchant_id',
        'reward_id',
        'points_used',
        'cash_topup',
        'claim_method',
        'status',
        'claim_code',
        'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'points_used' => 'decimal:2',
            'cash_topup' => 'decimal:2',
        ];
    }

    /**
     * Get the customer who made the redemption.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the merchant where the redemption was made.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the reward being redeemed.
     */
    public function reward()
    {
        return $this->belongsTo(MerchantReward::class, 'reward_id');
    }

    /**
     * Get the staff who processed the redemption.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
