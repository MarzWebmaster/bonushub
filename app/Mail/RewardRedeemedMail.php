<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class RewardRedeemedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $rewardName,
        public int $pointsSpent,
        public string $merchantName,
    ) {}

    public function build(): Content
    {
        return new Content(
            htmlString: view('emails.reward-redeemed', [
                'customerName' => $this->customerName,
                'rewardName' => $this->rewardName,
                'pointsSpent' => $this->pointsSpent,
                'merchantName' => $this->merchantName,
            ])->render()
        );
    }
}