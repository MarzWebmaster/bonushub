<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViralTask extends Model
{
    protected $fillable = [
        'merchant_id', 'title', 'description', 'platform', 'task_type',
        'points_reward', 'requires_screenshot', 'status',
        'max_completions', 'current_completions', 'total_points_spent',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_screenshot' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    // Relationships
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'task_id');
    }

    public function approvedSubmissions()
    {
        return $this->hasMany(TaskSubmission::class, 'task_id')->where('status', 'approved');
    }

    // Helpers
    public function isAvailable(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->ends_at && $this->ends_at->isPast()) return false;
        if ($this->max_completions && $this->current_completions >= $this->max_completions) return false;
        return true;
    }

    public function hasCustomerSubmitted(int $customerId): bool
    {
        return $this->submissions()->where('customer_id', $customerId)->exists();
    }

    public function getCustomerSubmission(int $customerId)
    {
        return $this->submissions()->where('customer_id', $customerId)->first();
    }
}
