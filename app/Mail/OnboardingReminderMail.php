<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The stage is derived from the member's own state, so the wrong reminder
     * can never be sent. Stages: 'email' (unverified) or 'profile' (verified
     * but incomplete).
     */
    public string $stage;

    public function __construct(
        public User $recipient,
        public string $lang = 'en',
    ) {
        $this->stage = $recipient->email_verified_at === null ? 'email' : 'profile';
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'email' => [
                'ar' => 'تذكير: فعّل بريدك الإلكتروني في خطوبة',
                'en' => 'Reminder: Verify Your Email Address on Khotobah',
            ],
            'profile' => [
                'ar' => 'تذكير: أكمل ملفك الشخصي في خطوبة',
                'en' => 'Reminder: Complete Your Profile on Khotobah',
            ],
        ];

        return new Envelope(subject: $subjects[$this->stage][$this->lang] ?? $subjects[$this->stage]['en']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding-reminder',
            with: [
                'userName' => $this->recipient->name ?? $this->recipient->username,
                'actionUrl' => $this->stage === 'email' ? url('/email/verify') : url('/complete-profile'),
                'stage' => $this->stage,
                'lang' => $this->lang,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
    }
}
