<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'tier_global',
        'birthdate',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'registered_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the merchants this customer is tied to.
     */
    public function merchants()
    {
        return $this->belongsToMany(Merchant::class, 'customer_merchant')
            ->withPivot('points', 'tier_per_merchant', 'tied_at')
            ->withTimestamps();
    }

    /**
     * Get the points transactions for this customer.
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }

    /**
     * Get the redemptions for this customer.
     */
    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}
