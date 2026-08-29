<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class TaskApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $taskTitle,
        public int $points,
        public string $merchantName,
    ) {}

    public function build(): Content
    {
        return new Content(
            htmlString: view('emails.task-approved', [
                'customerName' => $this->customerName,
                'taskTitle' => $this->taskTitle,
                'points' => $this->points,
                'merchantName' => $this->merchantName,
            ])->render()
        );
    }
}