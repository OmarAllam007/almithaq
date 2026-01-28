<?php

namespace App\Services;

use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\App;

class PaymentService
{
    public static function getGateway(): PaymentServiceInterface
    {
        $gateway = config('payment.default_gateway', 'paymob');

        return match ($gateway) {
            'paymob' => App::make(PaymobService::class),
            'myfatoorah' => App::make(MyFatoorahService::class),
            default => throw new \RuntimeException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
