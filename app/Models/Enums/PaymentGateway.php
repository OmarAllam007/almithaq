<?php

namespace App\Models\Enums;

enum PaymentGateway: string
{
    case PAYMOB = 'paymob';
    case MYFATOORAH = 'myfatoorah';
}
