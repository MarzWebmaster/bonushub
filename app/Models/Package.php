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
     * Feature labels as a plain list, whatever shape was stored: a list of labels (package form), a flag map
     * {"multi_branch": true} (seed data, possibly JSON-encoded twice) or nothing.
     *
     * @return string[]
     */
    public function featureList(): array
    {
        $f = $this->features;
        if (is_string($f)) {
            $decoded = json_decode($f, true);
            $f = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($f)) {
            return [];
        }
        $out = [];
        foreach ($f as $key => $value) {
            if (is_int($key)) {
                if (is_string($value) && trim($value) !== '') {
                    $out[] = trim($value);
                }
            } elseif ($value) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $out[] = preg_replace_callback('/\b(csv|api|qr|sms|ai|pos|pdf|ssm|ic)\b/i', fn ($m) => strtoupper($m[1]), $label);
            }
        }

        return $out;
    }

    /**
     * Get the merchants using this package.
     */
    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }
}
