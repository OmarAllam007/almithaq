<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }
    $authorized = $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;

    return $authorized;
});

Broadcast::channel('conversation.{conversationId}.typing', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    return $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('online-users', function ($user) {
    if (auth()->check()) {
        return [
            'id' => $user->id,
            'name' => $user->username,
        ];
    }
});
