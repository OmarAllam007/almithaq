<?php

namespace App\Models\Enums;

enum MarriageType: int
{
    case FIRST_WIFE = 1;

    case SECOND_WIFE = 2;

    case ONLY_ONE_WIFE = 3;

    case ACCEPT_POLYGAMY = 4;

    public function label(): string
    {
        return match ($this) {
            self::FIRST_WIFE => trans('enums.marriage_type_first_wife'),
            self::SECOND_WIFE => trans('enums.marriage_type_second_wife'),
            self::ONLY_ONE_WIFE => trans('enums.marriage_type_only_one_wife'),
            self::ACCEPT_POLYGAMY => trans('enums.marriage_type_accept_polygamy'),
        };
    }
}
