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
            self::ALWAYS => 'Always',
            self::USUALLY => 'Usually',
            self::SOMETIMES => 'Sometimes',
            self::RARELY => 'Rarely',
            self::NEVER => 'Never',
            self::PREFER_NOT_TO_SAY => 'Prefer Not To Say',
        };
    }
}
