<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignLink extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'slug',
        'medium',
        'visits',
        'unique_visits',
        'registrations',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'visits' => 'integer',
        'unique_visits' => 'integer',
        'registrations' => 'integer',
        'expires_at' => 'datetime',
    ];

    // ── Relationships ──

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function customerMerchants(): HasMany
    {
        return $this->hasMany(CustomerMerchant::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    // ── Helpers ──

    public function getUrlAttribute(): string
    {
        return url('/r/' . $this->slug);
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->visits === 0) return 0;
        return round(($this->registrations / $this->visits) * 100, 1);
    }

    /** Registrations as a share of UNIQUE visitors (a person who clicks ten times is still one visitor). */
    public function getUniqueConversionRateAttribute(): float
    {
        if ($this->unique_visits === 0) return 0;
        return min(100, round(($this->registrations / $this->unique_visits) * 100, 1));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Generate a unique short slug from merchant name + campaign name.
     */
    public static function generateSlug(string $merchantName, string $campaignName): string
    {
        $merchantPart = Str::slug(substr($merchantName, 0, 8), '');
        $campaignPart = Str::slug(substr($campaignName, 0, 8), '');
        $base = $merchantPart . '-' . $campaignPart;

        // Ensure uniqueness
        $slug = $base;
        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Record a click (visits +1). With a visitor hash it also keeps one row per distinct visitor and bumps
     * unique_visits the first time that visitor is seen on this link.
     */
    public function recordVisit(?string $visitorHash = null): void
    {
        $this->increment('visits');
        if ($visitorHash === null) {
            return;
        }
        $now = now();
        $inserted = DB::table('campaign_link_visitors')->insertOrIgnore([
            'campaign_link_id' => $this->id, 'visitor_hash' => $visitorHash, 'hits' => 1, 'first_seen_at' => $now, 'last_seen_at' => $now,
        ]);
        if ($inserted) {
            $this->increment('unique_visits');
        } else {
            DB::table('campaign_link_visitors')->where('campaign_link_id', $this->id)->where('visitor_hash', $visitorHash)
                ->update(['hits' => DB::raw('hits + 1'), 'last_seen_at' => $now]);
        }
    }

    /**
     * Record a registration (increment registrations counter).
     */
    public function recordRegistration(): void
    {
        $this->increment('registrations');
    }
}
