<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $ip = '37.242.69.0';
        $userAgent = request()->userAgent();

        if ($ip === '127.0.0.1') {
            return;
        }

        $geo = $this->lookupIp($ip);

        $event->user->loginLogs()->create([
            'ip_address' => $ip,
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'city' => $geo['city'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'is_vpn' => $geo['is_vpn'] ?? false,
            'user_agent' => $userAgent,
            'logged_at' => now(),
        ]);

    }

    protected function lookupIp(string $ip): array
    {
        $response = \Http::get("http://ip-api.com/json/{$ip}");

        if (! $response->successful()) {
            return [];
        }

        return [
            'country' => $response['country'] ?? null,
            'country_code' => $response['countryCode'] ?? null,
            'city' => $response['city'] ?? null,
            'isp' => $response['org'] ?? null,
            'is_vpn' => false,
        ];
    }
}
