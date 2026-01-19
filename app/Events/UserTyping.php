<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $typerId;

    public $conversationId;

    public function __construct($conversationId, $typerId)
    {
        $this->typerId = $typerId;
        $this->conversationId = $conversationId;
    }

    public function broadcastAs(): string
    {
        return 'UserTyping';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->conversationId}.typing"),
        ];
    }
}
