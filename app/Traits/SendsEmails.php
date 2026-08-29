<?php

namespace App\Traits;

use App\Mail\TaskApprovedMail;
use App\Mail\PointsEarnedMail;
use App\Mail\RewardRedeemedMail;
use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

trait SendsEmails
{
    public function sendTaskApprovedEmail(int $customerId, string $taskTitle, int $points, int $merchantId): void
    {
        try {
            $customer = Customer::find($customerId);
            $merchant = Merchant::find($merchantId);
            if (!$customer || !$customer->email || !$merchant) return;

            Mail::to($customer->email)->queue(new TaskApprovedMail(
                $customer->name,
                $taskTitle,
                $points,
                $merchant->name
            ));
            Log::info("Task approved email queued for customer #{$customerId}");
        } catch (\Exception $e) {
            Log::error("Failed to queue task approved email: " . $e->getMessage());
        }
    }

    public function sendPointsEarnedEmail(int $customerId, int $points, int $newBalance, int $merchantId, string $source): void
    {
        try {
            $customer = Customer::find($customerId);
            $merchant = Merchant::find($merchantId);
            if (!$customer || !$customer->email || !$merchant) return;

            Mail::to($customer->email)->queue(new PointsEarnedMail(
                $customer->name,
                $points,
                $newBalance,
                $merchant->name,
                $source
            ));
            Log::info("Points earned email queued for customer #{$customerId}");
        } catch (\Exception $e) {
            Log::error("Failed to queue points earned email: " . $e->getMessage());
        }
    }

    public function sendRewardRedeemedEmail(int $customerId, string $rewardName, int $pointsSpent, int $merchantId): void
    {
        try {
            $customer = Customer::find($customerId);
            $merchant = Merchant::find($merchantId);
            if (!$customer || !$customer->email || !$merchant) return;

            Mail::to($customer->email)->queue(new RewardRedeemedMail(
                $customer->name,
                $rewardName,
                $pointsSpent,
                $merchant->name
            ));
            Log::info("Reward redeemed email queued for customer #{$customerId}");
        } catch (\Exception $e) {
            Log::error("Failed to queue reward redeemed email: " . $e->getMessage());
        }
    }
}