<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Password_Reset_Request extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $url)
    {
        $this->token = $token;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return
        $this->subject('Reset Password')
            ->markdown('emails.reset_request')
            ->with('url', $this->url);
    }
}
