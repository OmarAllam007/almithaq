<?php

namespace App\Models\Enums;

enum PrayerType: int
{
    case ALWAYS = 1;

    case USUALLY = 2;

    case SOMETIMES = 3;

    case RARELY = 4;

    case NEVER = 5;

    case PREFER_NOT_TO_SAY = 6;

    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => trans('enums.prayer_always'),
            self::USUALLY => trans('enums.prayer_usually'),
            self::SOMETIMES => trans('enums.prayer_sometimes'),
            self::RARELY => trans('enums.prayer_rarely'),
            self::NEVER => trans('enums.prayer_never'),
            self::PREFER_NOT_TO_SAY => trans('enums.prayer_prefer_not_to_say'),
        };
    }
}
