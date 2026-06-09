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
            self::UNEMPLOYED => trans('enums.income_unemployed'),
            self::LESS_THAN_100_DOLLAR => trans('enums.income_less_than_100'),
            self::BETWEEN_200_1000_DOLLAR => trans('enums.income_200_1000'),
            self::BETWEEN_1000_2000_DOLLAR => trans('enums.income_1000_2000'),
            self::BETWEEN_3000_4000_DOLLAR => trans('enums.income_3000_4000'),
            self::BETWEEN_4000_5000_DOLLAR => trans('enums.income_4000_5000'),
            self::MORE_THAN_5000_DOLLAR => trans('enums.income_more_than_5000'),
            self::PREFER_NOT_TO_SAY => trans('enums.income_prefer_not_to_say'),
        };
    }
}
