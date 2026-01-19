<?php

namespace App\Models\Enums;

enum DevotionType: int
{
    case VERY_RELIGIOUS = 1;

    case RELIGIOUS = 2;

    case MODERATE = 3;

    case NOT_RELIGIOUS = 4;

    case PREFER_NOT_TO_SAY = 5;

    public function label(): string
    {
        return match ($this) {
            self::VERY_RELIGIOUS => 'Very Religious',
            self::RELIGIOUS => 'Religious',
            self::MODERATE => 'Moderate',
            self::NOT_RELIGIOUS => 'Not Religious',
            self::PREFER_NOT_TO_SAY => 'Prefer Not To Say',
        };
    }
}
