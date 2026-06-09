<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\Conversation;
use App\Models\Enums\MarriageStatus;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class InboxController extends Controller
{
    public function index()
    {
        $paginatedConversations = auth()->user()->conversations()
            ->has('messages')
            ->with(['userOne.mainProfileImage', 'userTwo.mainProfileImage', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $data = $paginatedConversations->through(function (Conversation $conversation) {
            $lastMessage = $conversation->latestMessage->first();

            $otherUser = auth()->id() === $conversation->user_two_id
                ? (new UserProfileResource($conversation->userOne))->resolve()
                : (new UserProfileResource($conversation->userTwo))->resolve();

            return [
                'id' => $conversation->id,
                'lastMessage' => $lastMessage ? [
                    'id' => $lastMessage->first()->id,
                    'message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at,
                    'isRead' => $lastMessage->isRead(auth()->id()),
                ] : null,
                'otherUser' => $otherUser,
                //                    [
                //                        'id'=> $otherUser['id'],
                //                        'name'=> $otherUser['name'],
                //                        'username'=> $otherUser['username'],
                //                        'marriage_status'=> MarriageStatus::tryFrom($otherUser['marriage_status'])?->label(),
                //                        'age'=> $otherUser->age,
                //
                //                        'mainProfileImage' => $mainImage?->thumbnail_url,
                //                        'isFavourite' => $otherUser->isFavoritedBy(auth()->id()),
                //                        'isIgnored' => $otherUser->isIgnored(auth()->id()),
                //                        'isOnline' => (bool) Redis::zscore('online_users', $otherUser->id),
                //                    ],
                //                    array_merge(
                //
                //                    $otherUser->only('id', 'name', 'username', 'marriage_status', 'age', 'nationality'),
                //                    [
                //
                //
                //                    ]
                //                ),
            ];
        });

        return Inertia::render('Inbox/InboxIndex', [
            'conversations' => $data,
        ]);
    }
}
