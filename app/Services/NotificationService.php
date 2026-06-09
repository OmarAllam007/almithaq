<?php

namespace App\Services;

use App\Events\UserNotificationSent;
use App\Models\Enums\NotificationType;
use App\Models\User;
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
        $notification = UserNotification::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'type' => $type,
            'data' => $data,
        ]);

        $broadcastTypes = [
            NotificationType::LIKE,
            NotificationType::NEW_MESSAGE,
            NotificationType::IMAGE_REQUEST,
            NotificationType::IMAGE_REQUEST_APPROVED,
            NotificationType::IMAGE_REQUEST_REJECTED,
        ];

        if ($actorId && in_array($type, $broadcastTypes, strict: true)) {
            $actor = User::with('mainProfileImage')->find($actorId);
            if ($actor) {
                broadcast(new UserNotificationSent($notification, $actor))->toOthers();
            }
        }

        return $notification;
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

    public function notifyImageRequest(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::IMAGE_REQUEST, $actorId);
    }

    public function notifyImageRequestApproved(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::IMAGE_REQUEST_APPROVED, $actorId);
    }

    public function notifyImageRequestRejected(int $userId, int $actorId): UserNotification
    {
        return $this->notify($userId, NotificationType::IMAGE_REQUEST_REJECTED, $actorId);
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
