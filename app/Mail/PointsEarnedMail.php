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
class PointsEarnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public int $points,
        public int $newBalance,
        public string $merchantName,
        public string $source,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Anda mendapat {$this->points} mata — {$this->merchantName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.points-earned',
            with: [
                'customerName' => $this->customerName,
                'points' => $this->points,
                'newBalance' => $this->newBalance,
                'merchantName' => $this->merchantName,
                'source' => $this->source,
            ],
        );
    }
}
