<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Warning (queued) sent once, `points.warn_days` before a customer's points with a merchant lapse. */
class PointsExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $merchantName,
        public int $points,
        public string $expiresOn,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "⏳ Mata anda di {$this->merchantName} akan tamat tempoh");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.points-expiry-warning',
            with: [
                'customerName' => $this->customerName,
                'merchantName' => $this->merchantName,
                'points' => $this->points,
                'expiresOn' => $this->expiresOn,
            ],
        );
    }
}
