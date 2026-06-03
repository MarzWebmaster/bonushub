<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InAppNotification;
use App\Models\Merchant;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Central notification service for BonusHub.
 *
 * All notification sending flows through here. Channels (email, sms, whatsapp,
 * in_app) are gated by NotificationSetting — SA toggles determine what fires.
 *
 * Usage:
 *   BonusNotifier::toMerchant($merchant, 'merchant_registration', [...]);
 *   BonusNotifier::toCustomer($customer, 'points_earned', [...]);
 *   BonusNotifier::blast($merchant, 'blast', 'Promo 50% off!', $customerIds);
 */
class BonusNotifier
{
    // ────────────────────────────────────────
    //  1-TO-1 NOTIFICATIONS
    // ────────────────────────────────────────

    /**
     * Notify a single merchant.
     *
     * @param  Merchant  $merchant
     * @param  string    $event   e.g. 'merchant_registration'
     * @param  array     $data    context (subject, body, sms_text, whatsapp_text, etc.)
     * @return array              channels that fired
     */
    public static function toMerchant(Merchant $merchant, string $event, array $data): array
    {
        // Find the merchant's User account (first admin)
        $user = User::where('merchant_id', $merchant->id)
            ->where('status', 'active')
            ->first();

        if (!$user) {
            Log::warning("BonusNotifier: No active user found for merchant #{$merchant->id}");
            return [];
        }

        return static::send($user, $event, $data, [
            'merchant_name' => $merchant->company_name,
        ]);
    }

    /**
     * Notify a single customer.
     *
     * @param  Customer  $customer
     * @param  string    $event    e.g. 'points_earned', 'tier_upgrade'
     * @param  array     $data     context
     * @return array               channels that fired
     */
    public static function toCustomer(Customer $customer, string $event, array $data): array
    {
        $user = User::where('email', $customer->email)
            ->where('status', 'active')
            ->first();

        if (!$user) {
            Log::warning("BonusNotifier: No active user found for customer #{$customer->id}");
            return [];
        }

        return static::send($user, $event, $data, [
            'customer_name' => $customer->name,
        ]);
    }

    // ────────────────────────────────────────
    //  BLAST (mass notification to customers)
    // ────────────────────────────────────────

    /**
     * Blast a notification to multiple customers under a merchant.
     *
     * @param  Merchant     $merchant
     * @param  string       $event    always 'blast' for blast-type
     * @param  string       $message  the blast message content
     * @param  array|null   $customerIds  null = all tied customers; array = specific IDs
     * @return int                    number of recipients queued
     */
    public static function blast(Merchant $merchant, string $event, string $message, ?array $customerIds = null): int
    {
        $query = $merchant->customers();
        if ($customerIds !== null) {
            $query->whereIn('customers.id', $customerIds);
        }
        $customers = $query->get();
        $count = 0;

        foreach ($customers as $customer) {
            $user = User::where('email', $customer->email)
                ->where('status', 'active')
                ->first();

            if (!$user) continue;

            static::send($user, 'blast', [
                'title'      => "Pemberitahuan dari {$merchant->company_name}",
                'message'    => $message,
                'sms_text'   => "[{$merchant->company_name}] {$message}",
                'whatsapp_text' => "*{$merchant->company_name}* \n{$message}",
            ], [
                'merchant_name' => $merchant->company_name,
            ]);

            $count++;
        }

        Log::info("BonusNotifier: Blast sent to {$count} customers from merchant #{$merchant->id}");
        return $count;
    }

    // ────────────────────────────────────────
    //  CORE SEND ENGINE
    // ────────────────────────────────────────

    /**
     * Send notification through all enabled channels for a user+event.
     */
    private static function send(User $user, string $event, array $data, array $extra = []): array
    {
        $fired = [];

        // ── Email ──
        if (NotificationSetting::isEnabled('email', $event)) {
            try {
                $subject = $data['subject'] ?? $data['title'] ?? "BonusHub Notification";
                $body    = $data['body'] ?? $data['message'] ?? '';

                Mail::raw($body, function ($mail) use ($user, $subject) {
                    $mail->to($user->email)
                        ->subject($subject);
                });

                $fired[] = 'email';
            } catch (\Throwable $e) {
                Log::error("BonusNotifier [email] failed: {$e->getMessage()}");
            }
        }

        // ── In-App ──
        if (NotificationSetting::isEnabled('in_app', $event)) {
            try {
                InAppNotification::create([
                    'user_id' => $user->id,
                    'title'   => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? $data['body'] ?? '',
                    'type'    => $data['type'] ?? 'info',
                    'data'    => array_merge($extra, $data['data'] ?? []),
                ]);
                $fired[] = 'in_app';
            } catch (\Throwable $e) {
                Log::error("BonusNotifier [in_app] failed: {$e->getMessage()}");
            }
        }

        // ── SMS (stub — real API later) ──
        if (NotificationSetting::isEnabled('sms', $event)) {
            try {
                $smsText = $data['sms_text'] ?? $data['message'] ?? '';
                // TODO: integrate SMS provider (Twilio / SMS gateway)
                Log::info("BonusNotifier [sms] → {$user->phone}: {$smsText}");
                $fired[] = 'sms';
            } catch (\Throwable $e) {
                Log::error("BonusNotifier [sms] failed: {$e->getMessage()}");
            }
        }

        // ── WhatsApp (stub — real API later) ──
        if (NotificationSetting::isEnabled('whatsapp', $event)) {
            try {
                $waText = $data['whatsapp_text'] ?? $data['message'] ?? '';
                // TODO: integrate WhatsApp Cloud API / WATI
                Log::info("BonusNotifier [whatsapp] → {$user->phone}: {$waText}");
                $fired[] = 'whatsapp';
            } catch (\Throwable $e) {
                Log::error("BonusNotifier [whatsapp] failed: {$e->getMessage()}");
            }
        }

        if (empty($fired)) {
            Log::info("BonusNotifier: No enabled channels for event '{$event}' — nothing sent.");
        }

        return $fired;
    }
}
