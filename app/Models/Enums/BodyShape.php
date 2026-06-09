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
            self::SLIM => trans('enums.body_shape_slim'),
            self::SPORTY => trans('enums.body_shape_sporty'),
            self::MEDIUM => trans('enums.body_shape_medium'),
            self::OBESE => trans('enums.body_shape_obese'),
            self::OVERWEIGHT => trans('enums.body_shape_overweight'),
        };
    }
}
