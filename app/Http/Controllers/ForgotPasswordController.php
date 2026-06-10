<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordResetRequest;
use App\Http\Requests\ForgotPasswordSendCodeRequest;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    private const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private WhatsAppService $whatsApp)
    {
        syncLangFiles(['login']);
    }

    public function sendCode(ForgotPasswordSendCodeRequest $request): JsonResponse
    {
        $channel = $request->validated('channel');
        $user = User::where('username', $request->validated('username'))->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => __('login.forgot-error-no-account'),
            ]);
        }

        if ($channel === 'email' && empty($user->email)) {
            throw ValidationException::withMessages([
                'channel' => __('login.forgot-error-no-email'),
            ]);
        }

        if ($channel === 'whatsapp' && (empty($user->country_code) || empty($user->phone_number))) {
            throw ValidationException::withMessages([
                'channel' => __('login.forgot-error-no-phone'),
            ]);
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::where('user_id', $user->id)->delete();
        PasswordResetCode::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        $destination = $channel === 'email'
            ? $this->dispatchEmail($user, $code)
            : $this->dispatchWhatsApp($user, $code);

        return response()->json([
            'channel' => $channel,
            'destination' => $destination,
            'expires_in_minutes' => self::CODE_TTL_MINUTES,
        ]);
    }

    public function reset(ForgotPasswordResetRequest $request): JsonResponse
    {
        $user = User::where('username', $request->validated('username'))->first();

        $record = $user
            ? PasswordResetCode::where('user_id', $user->id)->latest('id')->first()
            : null;

        if (! $user || ! $record || $record->isExpired()) {
            throw ValidationException::withMessages([
                'code' => __('login.forgot-error-invalid-code'),
            ]);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            $record->delete();

            throw ValidationException::withMessages([
                'code' => __('login.forgot-error-too-many-attempts'),
            ]);
        }

        if (! Hash::check($request->validated('code'), $record->code)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'code' => __('login.forgot-error-invalid-code'),
            ]);
        }

        $user->update(['password' => Hash::make($request->validated('password'))]);
        PasswordResetCode::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => __('login.forgot-success'),
        ]);
    }

    private function dispatchEmail(User $user, string $code): string
    {
        $user->notify(new PasswordResetCodeNotification($code));

        return $this->maskEmail($user->email);
    }

    private function dispatchWhatsApp(User $user, string $code): string
    {
        $fullPhone = $user->country_code.$user->phone_number;
        $this->whatsApp->sendResetCode($fullPhone, $code, App::getLocale());

        return $this->maskPhone($fullPhone);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email);
        $visible = mb_substr($name, 0, 1);

        return $visible.str_repeat('*', max(mb_strlen($name) - 1, 1)).'@'.$domain;
    }

    private function maskPhone(string $phone): string
    {
        $visible = mb_substr($phone, -4);

        return str_repeat('*', max(mb_strlen($phone) - 4, 0)).$visible;
    }
}
