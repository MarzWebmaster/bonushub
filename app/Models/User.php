<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'merchant_id',
        'branch_id',
        'phone',
        'status',
        'last_login_at',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the merchant that the user belongs to.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the points transactions processed by this user as staff.
     */
    public function processedPointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class, 'staff_id');
    }

    /**
     * Get the redemptions processed by this user as staff.
     */
    public function processedRedemptions()
    {
        return $this->hasMany(Redemption::class, 'staff_id');
    }

    /**
     * Get the points transactions approved by this user.
     */
    public function approvedPointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class, 'approved_by');
    }

    /**
     * Get the activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
