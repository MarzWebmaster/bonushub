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
        'score',
        'terms_accepted_at',
        'terms_hash',
    ];

    protected $casts = [
        'entry_count' => 'integer',
        'score' => 'integer',
        'terms_accepted_at' => 'datetime',
        'is_winner' => 'boolean',
        'won_at' => 'datetime',
    ];

    /** Leaderboard order: highest score first, earliest entrant wins ties (deterministic). */
    public function scopeRanked($query)
    {
        return $query->orderByDesc('score')->orderByDesc('entry_count')->orderBy('created_at')->orderBy('id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GiveawayCampaign::class, 'giveaway_campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
