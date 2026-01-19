<?php

namespace App\Models\Enums;

enum BodyShape: int
{
    case SLIM = 1;

    case SPORTY = 2;

    case MEDIUM = 3;

    case OBESE = 4;

    case OVERWEIGHT = 5;

    public function label(): string
    {
        return match ($this) {
            self::SLIM => 'Slim',
            self::SPORTY => 'Sporty',
            self::MEDIUM => 'Medium',
            self::OBESE => 'Obese',
            self::OVERWEIGHT => 'Overweight',
        };
    }
}
