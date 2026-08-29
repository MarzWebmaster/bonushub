<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiveawayCampaign extends Model
{
    protected $fillable = [
        'merchant_id',
        'title',
        'description',
        'prize_description',
        'prize_value',
        'max_entries',
        'winner_count',
        'status',
        'selection_method',
        'entry_method',
        'entries_per_referral',
        'starts_at',
        'ends_at',
        'winners_announced_at',
        'metadata',
    ];

    protected $casts = [
        'prize_value' => 'integer',
        'winner_count' => 'integer',
        'max_entries' => 'integer',
        'entries_per_referral' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'winners_announced_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GiveawayEntry::class, 'giveaway_campaign_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(GiveawayWinner::class, 'giveaway_campaign_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'draft')
            ->where('starts_at', '>', now());
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active'
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended'
            || ($this->ends_at && $this->ends_at->isPast());
    }

    public function participantCount(): int
    {
        return $this->entries()->distinct('customer_id')->count('customer_id');
    }

    public function totalEntries(): int
    {
        return $this->entries()->sum('entry_count');
    }
}
