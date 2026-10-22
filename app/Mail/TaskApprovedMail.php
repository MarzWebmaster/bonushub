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
class TaskApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $taskTitle,
        public int $points,
        public string $merchantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Tugasan anda diluluskan — {$this->merchantName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-approved',
            with: [
                'customerName' => $this->customerName,
                'taskTitle' => $this->taskTitle,
                'points' => $this->points,
                'merchantName' => $this->merchantName,
            ],
        );
    }
}
