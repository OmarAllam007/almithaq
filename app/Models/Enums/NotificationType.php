<?php

namespace App\Models\Enums;

enum NotificationType: string
{
    case LIKE = 'like';
    case IGNORE = 'ignore';
    case PROFILE_VISIT = 'profile_visit';
    case NEW_MESSAGE = 'new_message';
    case SUBSCRIPTION_RENEWED = 'subscription_renewed';
    case IMAGE_REQUEST = 'image_request';
    case IMAGE_REQUEST_APPROVED = 'image_request_approved';
    case IMAGE_REQUEST_REJECTED = 'image_request_rejected';

    public function label(): string
    {
        return match ($this) {
            self::LIKE => trans('home.liked your profile'),
            self::IGNORE => trans('home.ignored your profile'),
            self::PROFILE_VISIT => trans('home.visited your profile'),
            self::NEW_MESSAGE => trans('home.sent you a message'),
            self::SUBSCRIPTION_RENEWED => trans('home.subscription renewed'),
            self::IMAGE_REQUEST => trans('home.requested to view your images'),
            self::IMAGE_REQUEST_APPROVED => trans('home.approved your image request'),
            self::IMAGE_REQUEST_REJECTED => trans('home.rejected your image request'),
        };
    }
}
