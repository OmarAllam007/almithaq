<?php

namespace App\Models\Enums;

enum PaymentMethod: string
{
    case VISA = 'visa';
    case MADA = 'mada';
    case APPLE_PAY = 'apple_pay';
}
