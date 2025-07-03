<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $lastName;
    public $token;
    public $hotelName;
    public $setPasswordUrl;

    /**
     * Create a new message instance.
     * FIXED: URL format to use route parameter instead of query parameter
     */
    public function __construct($firstName, $lastName, $token, $hotelName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->token = $token;
        $this->hotelName = $hotelName;
        
        // FIXED: Use query parameter format ?token= to match existing routes
        $this->setPasswordUrl = url('/set-password?token=' . $token);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Créez votre mot de passe - ' . $this->hotelName)
                    ->view('emails.set-password')
                    ->with([
                        'firstName' => $this->firstName,
                        'lastName' => $this->lastName,
                        'hotelName' => $this->hotelName,
                        'setPasswordUrl' => $this->setPasswordUrl,
                        'token' => $this->token
                    ]);
    }
}

