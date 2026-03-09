<?php

namespace App\Services;

use App\Models\Enums\NotificationType;
use App\Models\UserNotification;

class NotificationService
{
    /**
     * Create a notification for a user.
     * This is the core method — extend by adding new NotificationType cases
     * and optionally adding convenience methods below.
     */
    public function notify(int $userId, NotificationType $type, ?int $actorId = null, ?array $data = null): UserNotification
    {
        return UserNotification::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'type' => $type,
            'data' => $data,
        ]);
    }

    public function notifyLike(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::LIKE, $actorId);
    }

    public function notifyIgnore(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::IGNORE, $actorId);
    }

    public function notifyProfileVisit(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::PROFILE_VISIT, $actorId);
    }

    public function notifyNewMessage(int $userId, int $actorId, int $conversationId): UserNotification
    {
        return $this->notify($userId, NotificationType::NEW_MESSAGE, $actorId, [
            'conversation_id' => $conversationId,
        ]);
    }

    public function notifySubscriptionRenewed(int $userId): UserNotification
    {
        return $this->notify($userId, NotificationType::SUBSCRIPTION_RENEWED);
    }

    public function getUnreadCount(int $userId): int
    {
        return UserNotification::where('user_id', $userId)->unread()->count();
    }

    public function markAsRead(UserNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllAsRead(int $userId): void
    {
        UserNotification::where('user_id', $userId)->unread()->update(['read_at' => now()]);
    }
}
