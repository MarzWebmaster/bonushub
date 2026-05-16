<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'target_type',
        'target_id',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
        ];
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
