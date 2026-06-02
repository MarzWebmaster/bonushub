<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantTier extends Model
{
    protected $fillable = ['merchant_id', 'tier_name', 'min_points'];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
        ];
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Resolve tier name for a given points total.
     * Tiers must be sorted by min_points desc before calling.
     */
    public static function resolveTier(int $merchantId, float $points): string
    {
        $tiers = static::where('merchant_id', $merchantId)
            ->orderBy('min_points', 'desc')
            ->get();

        foreach ($tiers as $tier) {
            if ($points >= $tier->min_points) {
                return $tier->tier_name;
            }
        }

        return 'Basic';
    }
}
