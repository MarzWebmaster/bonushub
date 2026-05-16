<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'price',
        'branch_limit',
        'staff_limit',
        'giveaway_limit',
        'task_limit',
        'features',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'json',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the merchants using this package.
     */
    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }
}
