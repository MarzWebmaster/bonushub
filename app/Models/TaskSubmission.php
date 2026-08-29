<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_id', 'customer_id', 'proof_url', 'screenshot_path',
        'status', 'points_awarded', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->whereHas('task', fn($q) => $q->where('merchant_id', $merchantId));
    }

    // Relationships
    public function task()
    {
        return $this->belongsTo(ViralTask::class, 'task_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
