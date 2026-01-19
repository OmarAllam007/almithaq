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
            self::ELEMENTARY_SCHOOL => 'Elementary school',
            self::HIGH_SCHOOL => 'High school',
            self::UNDERGRADUATE => 'Undergraduate',
            self::GRADUATE => 'Graduate',
            self::DOCTORAL => 'Doctoral',
            self::SELF_STUDY => 'Self study',
        };
    }
}
