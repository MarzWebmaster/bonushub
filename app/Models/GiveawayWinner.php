<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiveawayWinner extends Model
{
    protected $fillable = [
        'giveaway_campaign_id',
        'customer_id',
        'giveaway_entry_id',
        'position',
        'prize_description',
        'status',
        'notes',
        'claimed_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'claimed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GiveawayCampaign::class, 'giveaway_campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(GiveawayEntry::class, 'giveaway_entry_id');
    }
}
