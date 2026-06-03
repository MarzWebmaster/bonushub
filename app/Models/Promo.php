<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promo extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'type',
        'value',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
