<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $password;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $password = null)
    {
        try {
            $this->user = $user;
            $this->password = $password;
        } catch (\Exception $e) {
            Log::error('WelcomeMail::__construct failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): ?Envelope
    {
        try {
            return new Envelope(
                subject: 'Welcome Mail',
            );
        } catch (\Exception $e) {
            Log::error('WelcomeMail::envelope failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): ?Content
    {
        try {
            return new Content(
                view: 'emails.welcome',
            );
        } catch (\Exception $e) {
            Log::error('WelcomeMail::content failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): ?array
    {
        try {
            return [];
        } catch (\Exception $e) {
            Log::error('WelcomeMail::attachments failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
