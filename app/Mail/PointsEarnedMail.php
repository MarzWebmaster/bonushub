<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

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

    public function build(): Content
    {
        return new Content(
            htmlString: view('emails.points-earned', [
                'customerName' => $this->customerName,
                'points' => $this->points,
                'newBalance' => $this->newBalance,
                'merchantName' => $this->merchantName,
                'source' => $this->source,
            ])->render()
        );
    }
}