<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class MerchantDocSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $merchantName,
        public string $ownerName,
        public string $email,
        public string $phone,
        public ?string $icFront,
        public ?string $icBack,
        public ?string $ssmImage,
    ) {}

    public function build(): Content
    {
        return new Content(
            htmlString: view('emails.merchant-doc-submitted', [
                'merchantName' => $this->merchantName,
                'ownerName'    => $this->ownerName,
                'email'        => $this->email,
                'phone'        => $this->phone,
                'icFront'      => $this->icFront,
                'icBack'       => $this->icBack,
                'ssmImage'     => $this->ssmImage,
            ])->render()
        );
    }
}
