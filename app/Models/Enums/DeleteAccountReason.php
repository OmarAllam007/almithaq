<?php

namespace App\Models\Enums;

enum DeleteAccountReason
{
    case I_GOT_WHAT_I_WANT_FROM_THE_APP;
    case I_GOT_WHAT_I_WANT_OUTSIDE_THE_APP;
    case PERSONAL_REASON;
}
