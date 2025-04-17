<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $password;
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }
    public function build()
    {
        return $this->subject('Your New Password')
                    ->view('emails.password-reset')
                    ->with([
                        'user' => $this->user,
                        'password' => $this->password,
                    ]);
    }
}
