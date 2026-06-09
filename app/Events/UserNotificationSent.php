<?php

namespace App\Events;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly UserNotification $notification,
        public readonly User $actor,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.'.$this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'UserNotificationSent';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->notification->type->value,
            'actor_id' => $this->actor->id,
            'actor_username' => $this->actor->username,
            'actor_avatar' => $this->actor->mainProfileImage->first()?->thumbnail_url,
            'data' => $this->notification->data,
        ];
    }
}
