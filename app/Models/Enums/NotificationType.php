<?php

namespace App\Models\Enums;

enum NotificationType: string
{
    case LIKE = 'like';
    case IGNORE = 'ignore';
    case PROFILE_VISIT = 'profile_visit';
    case NEW_MESSAGE = 'new_message';
    case SUBSCRIPTION_RENEWED = 'subscription_renewed';
}
