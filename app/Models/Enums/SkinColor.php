<?php

namespace App\Models\Enums;

enum SkinColor: int
{
    case White = 1;

    case WHEATISH_WHITE = 2;

    case Light_BROWN = 3;

    case BROWN = 4;

    case DARK_BROWN = 5;

    case Black = 6;

    public function label(): string
    {
        return match ($this) {
            self::White => 'White',
            self::WHEATISH_WHITE => 'Wheatish White',
            self::Light_BROWN => 'Light Brown',
            self::BROWN => 'Brown',
            self::DARK_BROWN => 'Dark Brown',
            self::Black => 'Black',
        };
    }
}
