<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orderCode;

    /**
     * Create a new message instance.
     */
    public function __construct($orderCode)
    {
        $this->orderCode = $orderCode;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Order Confirmation - Sugar-Papi')
                    ->view('emails.Orderplaced') // 👈 resources/views/emails/Orderplaced.php.blade.php
                    ->with([
                        'orderCode' => $this->orderCode,
                    ]);
    }
}
