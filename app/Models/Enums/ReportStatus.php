<?php

namespace App\Models\Enums;

enum ReportStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case RESOLVED = 'resolved';
    case DISMISSED = 'dismissed';
}
