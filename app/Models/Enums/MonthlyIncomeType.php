<?php

namespace App\Models\Enums;

enum MonthlyIncomeType : int
{
    case UNEMPLOYED = 1;

    case LESS_THAN_100_DOLLAR = 2;

    case BETWEEN_200_1000_DOLLAR = 3;

    case BETWEEN_1000_2000_DOLLAR = 4;

    case BETWEEN_3000_4000_DOLLAR = 5;

    case BETWEEN_4000_5000_DOLLAR = 6;

    case MORE_THAN_5000_DOLLAR = 7;

    case PREFER_NOT_TO_SAY = 8;

    public function label(): string
    {
        return match ($this) {
            self::UNEMPLOYED => 'UNEMPLOYED',
            self::LESS_THAN_100_DOLLAR => 'LESS_THAN_100_DOLLAR',
            self::BETWEEN_200_1000_DOLLAR => 'BETWEEN_200_1000_DOLLAR',
            self::BETWEEN_1000_2000_DOLLAR => 'BETWEEN_1000_2000_DOLLAR',
            self::BETWEEN_3000_4000_DOLLAR => 'BETWEEN_3000_4000_DOLLAR',
            self::BETWEEN_4000_5000_DOLLAR => 'BETWEEN_4000_5000_DOLLAR',
            self::MORE_THAN_5000_DOLLAR => 'MORE_THAN_5000_DOLLAR',
            self::PREFER_NOT_TO_SAY => 'PREFER_NOT_TO_SAY',
        };
    }
}
