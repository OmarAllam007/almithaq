<?php

namespace App\Models\Enums;

enum MarriageStatus: int
{
    case SINGLE = 1;

    case MARRIED = 2;

    case DIVORCED = 3;

    case WIDOWED = 4;

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Single',
            self::MARRIED => 'Married',
            self::DIVORCED => 'Divorced',
            self::WIDOWED => 'Widowed',
            default => throw new \Exception('Unexpected match value'),
        };
    }
}
