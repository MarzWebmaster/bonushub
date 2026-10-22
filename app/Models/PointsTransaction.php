<?php

namespace App\Models;

use App\Services\TierService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PointsTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'merchant_id',
        'branch_id',
        'staff_id',
        'type',
        'points',
        'amount_spent',
        'status',
        'approved_by',
        'notes',
    ];

    protected static function booted(): void
    {
        // PRD v1.2 7.2: an approved earn can lift the customer's tier (upgrade-only; never breaks the save)
        static::saved(function (PointsTransaction $t) {
            if ($t->type === 'earn' && $t->status === 'approved' && ($t->wasRecentlyCreated || $t->wasChanged('status'))) {
                try {
                    app(TierService::class)->refresh((int) $t->customer_id, (int) $t->merchant_id);
                } catch (\Throwable $e) {
                    Log::warning('Tier refresh failed: ' . $e->getMessage());
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'amount_spent' => 'decimal:2',
        ];
    }

    /**
     * Get the customer associated with this transaction.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the merchant associated with this transaction.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the branch where the transaction occurred.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the staff who processed the transaction.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the user who approved the transaction.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
