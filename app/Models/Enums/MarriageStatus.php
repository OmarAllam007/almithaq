<?php

namespace App\Models\Enums;

enum MarriageStatus: int
{
    case SINGLE = 1;

    case MARRIED = 2;

    case DIVORCED = 3;

    case WIDOWED = 4;

    public function label(): string
    {
        return $this->labelForGender(true);
    }

    public function labelForGender(bool $isMale): string
    {
        $suffix = $isMale ? 'male' : 'female';

        return match ($this) {
            self::SINGLE => trans("enums.marriage_status_single_{$suffix}"),
            self::MARRIED => trans("enums.marriage_status_married_{$suffix}"),
            self::DIVORCED => trans("enums.marriage_status_divorced_{$suffix}"),
            self::WIDOWED => trans("enums.marriage_status_widowed_{$suffix}"),
        };
    }
}
