<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'address',
        'phone',
        'status',
    ];

    /**
     * Get the merchant that owns the branch.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the users assigned to this branch.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the points transactions for this branch.
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }
}
