<?php

namespace App\Models\Enums;

enum FinancialStatus: int
{
    case POOR = 1;

    case AVERAGE = 2;

    case IMPROVED_FINANCIAL_SITUATION = 3;

    case RICH = 4;

    case WEALTHY = 5;

    public function label(): string
    {
        return match ($this) {
            self::POOR => trans('enums.financial_status_poor'),
            self::AVERAGE => trans('enums.financial_status_average'),
            self::IMPROVED_FINANCIAL_SITUATION => trans('enums.financial_status_improved'),
            self::RICH => trans('enums.financial_status_rich'),
            self::WEALTHY => trans('enums.financial_status_wealthy'),
        };
    }
}
