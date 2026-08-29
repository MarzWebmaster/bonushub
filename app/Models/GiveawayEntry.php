<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiveawayEntry extends Model
{
    protected $fillable = [
        'giveaway_campaign_id',
        'customer_id',
        'entry_count',
        'source',
        'source_reference',
        'is_winner',
        'prize_won',
        'won_at',
    ];

    protected $casts = [
        'entry_count' => 'integer',
        'is_winner' => 'boolean',
        'won_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GiveawayCampaign::class, 'giveaway_campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
