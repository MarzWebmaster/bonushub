<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
