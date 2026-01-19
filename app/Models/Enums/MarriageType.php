<?php

namespace App\Models\Enums;

enum MarriageType : int
{
    case FIRST_WIFE = 1;

    case SECOND_WIFE = 2;

    case ONLY_ONE_WIFE = 3;

    case ACCEPT_POLYGAMY = 4;

    public function label(): string
    {
        return match ($this) {
            self::FIRST_WIFE => 'First wife',
            self::SECOND_WIFE => 'Second wife',
            self::ONLY_ONE_WIFE => 'Only one wife',
            self::ACCEPT_POLYGAMY => 'Accept polygamy',
        };
    }
}
