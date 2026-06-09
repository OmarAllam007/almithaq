<?php

namespace App\Models\Enums;

enum EducationLevel: int
{
    case ELEMENTARY_SCHOOL = 1;

    case HIGH_SCHOOL = 2;

    case UNDERGRADUATE = 3;

    case GRADUATE = 4;

    case DOCTORAL = 5;

    case SELF_STUDY = 6;

    public function label(): string
    {
        return match ($this) {
            self::ELEMENTARY_SCHOOL => trans('enums.education_elementary_school'),
            self::HIGH_SCHOOL => trans('enums.education_high_school'),
            self::UNDERGRADUATE => trans('enums.education_undergraduate'),
            self::GRADUATE => trans('enums.education_graduate'),
            self::DOCTORAL => trans('enums.education_doctoral'),
            self::SELF_STUDY => trans('enums.education_self_study'),
        };
    }
}
