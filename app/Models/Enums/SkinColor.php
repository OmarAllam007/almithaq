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
            self::White => trans('enums.skin_color_white'),
            self::WHEATISH_WHITE => trans('enums.skin_color_wheatish_white'),
            self::Light_BROWN => trans('enums.skin_color_light_brown'),
            self::BROWN => trans('enums.skin_color_brown'),
            self::DARK_BROWN => trans('enums.skin_color_dark_brown'),
            self::Black => trans('enums.skin_color_black'),
        };
    }
}
