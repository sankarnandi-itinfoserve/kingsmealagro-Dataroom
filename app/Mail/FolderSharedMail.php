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

class FolderSharedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $folder;
    public $link;
    public $permission;

    public function __construct($folder, $link, $permission)
    {
        try {
            $this->folder = $folder;
            $this->link = $link;
            $this->permission = $permission;
        } catch (\Exception $e) {
            Log::error('FolderSharedMail::__construct failed: ' . $e->getMessage());
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
                subject: 'Folder Shared With You',
            );
        } catch (\Exception $e) {
            Log::error('FolderSharedMail::envelope failed: ' . $e->getMessage());
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
                view: 'emails.folder_shared',
            );
        } catch (\Exception $e) {
            Log::error('FolderSharedMail::content failed: ' . $e->getMessage());
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
            Log::error('FolderSharedMail::attachments failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
