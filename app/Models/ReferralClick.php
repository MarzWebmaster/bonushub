<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    protected $table = 'referral_clicks';

    protected $fillable = [
        'referral_id',
        'ip_address',
        'user_agent',
        'referer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}
