<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a one-time password reset code to a WhatsApp number.
     *
     * The actual WhatsApp Business API integration will be wired in here.
     * For now the message is logged so the flow is fully functional end-to-end.
     */
    public function sendResetCode(string $fullPhone, string $code, string $locale = 'en'): void
    {
        $message = $locale === 'ar'
            ? "رمز إعادة تعيين كلمة المرور الخاص بك هو: {$code}"
            : "Your password reset code is: {$code}";

        // TODO: Replace with the WhatsApp Business API call once credentials are provided.
        Log::info('WhatsApp reset code dispatched', [
            'phone' => $fullPhone,
            'message' => $message,
        ]);
    }
}
