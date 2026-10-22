<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent (queued) to a giveaway winner, and to a replacement winner when the first one did not claim. */
class GiveawayWinnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $prize,
        public string $campaignTitle,
        public string $merchantName,
        public ?string $claimBy,
        public bool $replacement = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "🎉 Anda menang — {$this->campaignTitle}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.giveaway-winner',
            with: [
                'customerName' => $this->customerName,
                'prize' => $this->prize,
                'campaignTitle' => $this->campaignTitle,
                'merchantName' => $this->merchantName,
                'claimBy' => $this->claimBy,
                'replacement' => $this->replacement,
            ],
        );
    }
}
