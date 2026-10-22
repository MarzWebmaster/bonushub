<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Laravel 11 style: envelope() + content(). The old build(): Content returned a Content object from build(), which
 * Laravel ignores, so the mail had neither a subject nor a body and could never be sent.
 */
class RewardRedeemedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $rewardName,
        public int $pointsSpent,
        public string $merchantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Tebusan ganjaran anda — {$this->merchantName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reward-redeemed',
            with: [
                'customerName' => $this->customerName,
                'rewardName' => $this->rewardName,
                'pointsSpent' => $this->pointsSpent,
                'merchantName' => $this->merchantName,
            ],
        );
    }
}
