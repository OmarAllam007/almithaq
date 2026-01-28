<?php

namespace App\Models\Enums;

enum DeleteAccountReason: int
{
    case IGotWhatIWantFromTheApp = 0;
    case IGotWhatIWantOutsideTheApp = 1;
    case PersonalReason = 2;

    public function label(): string
    {
        return match ($this) {
            self::IGotWhatIWantFromTheApp => 'I got what I want from the app',
            self::IGotWhatIWantOutsideTheApp => 'I got what I want outside the app',
            self::PersonalReason => 'Personal reason',
        };
    }
}
