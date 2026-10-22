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
        'terms',
        'threshold_score',
        'milestones',
        'claim_days',
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
        'threshold_score' => 'integer',
        'milestones' => 'array',
        'claim_days' => 'integer',
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

    public const METHOD_LABELS = [
        'top_referrers' => 'Top referrers',
        'threshold'     => 'Reach the target score',
        'milestone'     => 'Milestone ladder',
        'manual'        => 'Chosen by the merchant',
        'random'        => 'Random draw (disabled)',
    ];

    public function selectionLabel(): string
    {
        return self::METHOD_LABELS[$this->selection_method] ?? ucfirst((string) $this->selection_method);
    }

    /** Milestone ladder sorted from the highest score down: [['score' => int, 'prize' => string], ...] */
    public function milestoneLadder(): array
    {
        $rows = collect($this->milestones ?? [])
            ->filter(fn ($m) => isset($m['score'], $m['prize']))
            ->map(fn ($m) => ['score' => (int) $m['score'], 'prize' => (string) $m['prize']])
            ->sortByDesc('score')
            ->values()
            ->all();

        return $rows;
    }

    /** Prize of the highest milestone reached with $score, or null when none is reached. */
    public function milestonePrizeFor(int $score): ?string
    {
        foreach ($this->milestoneLadder() as $m) {
            if ($score >= $m['score']) {
                return $m['prize'];
            }
        }

        return null;
    }

    /** True once the campaign may be drawn: it was ended, or its end time has passed. */
    public function readyForDraw(): bool
    {
        return $this->status === 'ended'
            || ($this->status === 'active' && $this->ends_at && $this->ends_at->isPast());
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
