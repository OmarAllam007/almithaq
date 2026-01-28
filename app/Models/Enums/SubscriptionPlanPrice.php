<?php

namespace App\Models\Enums;

enum SubscriptionPlanPrice : int
{
    case MONTH_1 = 50;
    case MONTH_3 = 100;
    case MONTH_6 = 150;
    case MONTH_12 = 250;

    public static function getAllValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
