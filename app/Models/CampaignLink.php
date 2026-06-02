<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignLink extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'slug',
        'medium',
        'visits',
        'registrations',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'visits' => 'integer',
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
     * Record a visit (increment visits counter).
     */
    public function recordVisit(): void
    {
        $this->increment('visits');
    }

    /**
     * Record a registration (increment registrations counter).
     */
    public function recordRegistration(): void
    {
        $this->increment('registrations');
    }
}
