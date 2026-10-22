<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralSignup extends Model
{
    protected $fillable = [
        'referral_id',
        'referred_customer_id',
        'status',
        'invalid_reason',
        'signup_ip_hash',
        'flags',
        'points_referrer',
        'points_referred',
        'validated_at',
    ];

    protected $casts = [
        'flags' => 'array',
        'points_referrer' => 'integer',
        'points_referred' => 'integer',
        'validated_at' => 'datetime',
    ];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }
}
