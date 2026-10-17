<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $locale = app()->getLocale();
            $isArabic = $locale === 'ar';

            return (new MailMessage)
                ->subject($isArabic ? 'تأكيد بريدك الإلكتروني | خطوبة' : 'Verify Your Email Address | Khotobah')
                ->view('emails.verify-address', [
                    'verificationUrl' => $url,
                    'userName' => $notifiable->name ?: $notifiable->username,
                    'isArabic' => $isArabic,
                ]);
        });
    }
}
