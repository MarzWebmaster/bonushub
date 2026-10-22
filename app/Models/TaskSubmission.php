<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_id', 'customer_id', 'proof_url', 'screenshot_path',
        'status', 'points_awarded', 'reviewed_by', 'reviewed_at', 'review_notes',
        'screenshot_hash', 'proof_url_hash', 'duplicate_of_id',
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

    /** The earlier submission whose screenshot or link this one repeats (set when it was sent). */
    public function duplicateOf()
    {
        return $this->belongsTo(TaskSubmission::class, 'duplicate_of_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
