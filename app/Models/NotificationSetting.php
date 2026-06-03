<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'channel',
        'event_type',
        'enabled',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'config'  => 'array',
        ];
    }

    /**
     * Check if a channel is enabled for a given event type.
     */
    public static function isEnabled(string $channel, string $eventType): bool
    {
        return static::where('channel', $channel)
            ->where('event_type', $eventType)
            ->where('enabled', true)
            ->exists();
    }

    /**
     * Get config for a channel×event combo.
     */
    public static function getConfig(string $channel, string $eventType): ?array
    {
        return static::where('channel', $channel)
            ->where('event_type', $eventType)
            ->value('config');
    }

    /**
     * Seed default settings — called during migration or SA setup.
     */
    public static function seedDefaults(): void
    {
        $channels = ['email', 'sms', 'whatsapp', 'in_app'];
        $events = [
            'merchant_registration',
            'customer_registration',
            'points_earned',
            'reward_redeemed',
            'tier_upgrade',
            'blast',
        ];

        foreach ($channels as $channel) {
            foreach ($events as $event) {
                static::firstOrCreate(
                    ['channel' => $channel, 'event_type' => $event],
                    ['enabled' => in_array($channel, ['email', 'in_app']), 'config' => null]
                );
            }
        }
    }
}
