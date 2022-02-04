<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quote;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($quote, $pdf)
    {
        $this->quote = $quote;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject('Quotation Order')
            ->markdown('emails.quotation')
            ->attachData($this->pdf, 'Quotation_' . $this->quote['Ref'] . '.pdf', [
                'mime' => 'application/pdf',
            ])
            ->with('data', $this->quote);
    }
}
