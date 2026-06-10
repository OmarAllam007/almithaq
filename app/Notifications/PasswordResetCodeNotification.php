<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('login.forgot-email-subject'))
            ->greeting(__('login.forgot-email-greeting'))
            ->line(__('login.forgot-email-line'))
            ->line(__('login.forgot-email-code', ['code' => $this->code]))
            ->line(__('login.forgot-email-expiry'));
    }
}
