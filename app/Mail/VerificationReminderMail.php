<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $lang = 'en',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->lang === 'ar'
            ? 'تذكير: فعّل بريدك الإلكتروني في المثاق'
            : 'Reminder: Verify Your Email Address on AlMithaq';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-reminder',
            with: [
                'userName' => $this->recipient->name ?? $this->recipient->username,
                'verificationUrl' => url('/email/verify'),
                'lang' => $this->lang,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
