<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): ?array
    {
        try {
            return ['mail'];
        } catch (\Exception $e) {
            Log::error('ResetPasswordNotification::via failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toMail(object $notifiable): ?MailMessage
    {
        try {
            $resetUrl = url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Reset Your Password — ' . config('app.name'))
                ->view('emails.reset-password', [
                    'user'     => $notifiable,
                    'resetUrl' => $resetUrl,
                ]);
        } catch (\Exception $e) {
            Log::error('ResetPasswordNotification::toMail failed: ' . $e->getMessage());
            return null;
        }
    }
}
