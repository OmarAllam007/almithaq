<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class InboxController extends Controller
{
    public function index()
    {
        $paginatedConversations = auth()->user()->conversations()
            ->with(['userOne.mainProfileImage', 'userTwo.mainProfileImage', 'latestMessage'])
            ->paginate(20);

        $data = $paginatedConversations->through(function (Conversation $conversation) {
            $lastMessage = $conversation->latestMessage->first();

            $otherUser = auth()->id() === $conversation->user_two_id
                ? $conversation->userOne
                : $conversation->userTwo;

            $mainImage = $otherUser->mainProfileImage->first();

            return [
                'id' => $conversation->id,
                'lastMessage' => $lastMessage ? [
                    'id' => $lastMessage->first()->id,
                    'message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at,
                    'isRead' => $lastMessage->isRead(auth()->id()),
                ] : null,
                'otherUser' => array_merge(
                    $otherUser->only('id', 'name', 'username', 'marriage_status', 'age', 'nationality'),
                    [
                        'mainProfileImage' => $mainImage?->thumbnail_url,
                        'isFavourite' => $otherUser->isFavoritedBy(auth()->id()),
                        'isIgnored' => $otherUser->isIgnored(auth()->id()),
                        'isOnline' => (bool) Redis::zscore('online_users', $otherUser->id),
                    ]
                ),
            ];
        });

        return Inertia::render('Inbox/InboxIndex', [
            'conversations' => $data,
        ]);
    }
}
