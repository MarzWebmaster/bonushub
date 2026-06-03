<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = [
        'company_name',
        'logo',
        'phone',
        'address',
        'status',
        'package_id',
        'subscription_expiry',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expiry' => 'datetime',
        ];
    }

    /**
     * Get the package associated with this merchant.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the branches for this merchant.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the users (staff/admin) belonging to this merchant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the loyalty rates for this merchant.
     */
    public function loyaltyRate()
    {
        return $this->hasOne(LoyaltyRate::class);
    }

    /**
     * Get the rewards for this merchant.
     */
    public function rewards()
    {
        return $this->hasMany(MerchantReward::class);
    }

    /**
     * Get the customers tied to this merchant.
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_merchant')
            ->withPivot('points', 'tier_per_merchant', 'tied_at')
            ->withTimestamps();
    }

    /**
     * Get the points transactions for this merchant.
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }

    /**
     * Get the redemptions for this merchant.
     */
    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }
}
