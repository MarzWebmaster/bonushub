<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'merchant_id',
        'branch_id', 'phone', 'status', 'last_login_at', 'profile_picture',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function processedPointsTransactions() { return $this->hasMany(PointsTransaction::class, 'staff_id'); }
    public function processedRedemptions() { return $this->hasMany(Redemption::class, 'staff_id'); }
    public function approvedPointsTransactions() { return $this->hasMany(PointsTransaction::class, 'approved_by'); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }

    /**
     * Customer-related relationships for customer role users.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'email', 'email');
    }

    public function pointsBalances()
    {
        return $this->hasManyThrough(
            CustomerMerchant::class,
            Customer::class,
            'email',       // Foreign on customers
            'customer_id', // Foreign on customer_merchant
            'email',       // Local on users
            'id'           // Local on customers
        );
    }
}
