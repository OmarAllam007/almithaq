<?php

namespace App\Models\Enums;

enum MonthlyIncomeType: int
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
            self::UNEMPLOYED => 'Unemployed',
            self::LESS_THAN_100_DOLLAR => 'Less than $100',
            self::BETWEEN_200_1000_DOLLAR => '$200 - $1,000',
            self::BETWEEN_1000_2000_DOLLAR => '$1,000 - $2,000',
            self::BETWEEN_3000_4000_DOLLAR => '$3,000 - $4,000',
            self::BETWEEN_4000_5000_DOLLAR => '$4,000 - $5,000',
            self::MORE_THAN_5000_DOLLAR => 'More than $5,000',
            self::PREFER_NOT_TO_SAY => 'Prefer not to say',
        };
    }
}
