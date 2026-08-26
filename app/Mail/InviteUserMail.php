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

class InviteUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteLink;

    /**
     * Create a new message instance.
     */
    public function __construct($inviteLink)
    {
        try {
            $this->inviteLink = $inviteLink;
        } catch (\Exception $e) {
            Log::error('InviteUserMail::__construct failed: ' . $e->getMessage());
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
                subject: 'You are invited to join our ' . config('app.name'),
            );
        } catch (\Exception $e) {
            Log::error('InviteUserMail::envelope failed: ' . $e->getMessage());
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
                view: 'emails.invite-user',
            );
        } catch (\Exception $e) {
            Log::error('InviteUserMail::content failed: ' . $e->getMessage());
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
            Log::error('InviteUserMail::attachments failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
