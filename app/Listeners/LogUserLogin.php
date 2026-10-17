<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Http;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        $ip = request()->ip();

        if (! $this->isPublicIp($ip)) {
            return;
        }

        $geo = $this->lookupIp($ip);

        $event->user->loginLogs()->create([
            'ip_address' => $ip,
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'city' => $geo['city'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'is_vpn' => false,
            'user_agent' => request()->userAgent(),
            'logged_at' => now(),
        ]);
    }

    protected function isPublicIp(?string $ip): bool
    {
        return $ip !== null && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * @return array{country?: ?string, country_code?: ?string, city?: ?string, isp?: ?string}
     */
    protected function lookupIp(string $ip): array
    {
        $response = rescue(fn () => Http::timeout(3)->get("http://ip-api.com/json/{$ip}"), report: true);

        if (! $response?->successful() || ($response['status'] ?? null) !== 'success') {
            return [];
        }

        return [
            'country' => $response['country'] ?? null,
            'country_code' => $response['countryCode'] ?? null,
            'city' => $response['city'] ?? null,
            'isp' => $response['isp'] ?? $response['org'] ?? null,
        ];
    }
}
