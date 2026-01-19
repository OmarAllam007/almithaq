<?php

namespace App\Models\Enums;

enum ReportReason: string
{
    case INAPPROPRIATE_CONTENT = 'inappropriate_content';
    case HARASSMENT = 'harassment';
    case FAKE_PROFILE = 'fake_profile';
    case SPAM = 'spam';
    case SEXUAL_CONTENT = 'sexual_content';
    case OFFENSIVE_LANGUAGE = 'offensive_language';
    case OTHER = 'other';
}
