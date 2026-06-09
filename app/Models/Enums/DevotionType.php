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
            self::VERY_RELIGIOUS => trans('enums.devotion_very_religious'),
            self::RELIGIOUS => trans('enums.devotion_religious'),
            self::MODERATE => trans('enums.devotion_moderate'),
            self::NOT_RELIGIOUS => trans('enums.devotion_not_religious'),
            self::PREFER_NOT_TO_SAY => trans('enums.devotion_prefer_not_to_say'),
        };
    }
}
