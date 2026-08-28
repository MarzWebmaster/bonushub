<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = [
        'company_name',
        'owner_name',
        'business_type',
        'logo',
        'phone',
        'address',
        'status',
        'ic_image',
        'ssm_image',
        'consent_pdpa',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'package_id',
        'subscription_expiry',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expiry' => 'datetime',
            'verified_at'         => 'datetime',
            'consent_pdpa'        => 'boolean',
        ];
    }

    // ── Status Constants ──
    const STATUS_PENDING           = 'pending_verification';
    const STATUS_PENDING_APPROVAL  = 'pending_approval';
    const STATUS_ACTIVE     = 'active';
    const STATUS_SUSPENDED  = 'suspended';
    const STATUS_REJECTED   = 'rejected';

    // ── Scopes ──
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PENDING_APPROVAL]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if merchant is approved (active)
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
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

    /**
     * The superadmin who verified this merchant.
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
