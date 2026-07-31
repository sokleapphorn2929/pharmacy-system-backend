<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orderData;

    public function __construct($orderData)
    {
        $this->orderData = $orderData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Successful - Payment Paid',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.successful', // We will create this view next
        );
    }
}