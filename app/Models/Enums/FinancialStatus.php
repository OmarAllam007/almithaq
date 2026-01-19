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
            self::POOR => 'Poor',
            self::AVERAGE => 'Average',
            self::IMPROVED_FINANCIAL_SITUATION => 'Improved Financial Situation',
            self::RICH => 'Rich',
            self::WEALTHY => 'Wealthy',
        };
    }
}
