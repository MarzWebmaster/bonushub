<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerMerchant extends Pivot
{
    protected $table = 'customer_merchant';

    protected $fillable = [
        'customer_id',
        'merchant_id',
        'points',
        'tier_per_merchant',
        'tied_at',
        'campaign_link_id',
    ];

    protected $casts = [
        'tied_at' => 'datetime',
        'points' => 'integer',
    ];

    public $timestamps = true;

    /**
     * Get the customer for this pivot.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the merchant for this pivot.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the campaign link that brought this customer.
     */
    public function campaignLink()
    {
        return $this->belongsTo(CampaignLink::class);
    }
}
