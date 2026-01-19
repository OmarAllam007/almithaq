<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            //            'total_users' => User::count(),
            //            'total_posts' => Post::count(),
            //            'total_comments' => Comment::count(),
        ];
        syncLangFiles(['home']);

        $userId = Auth::id();

        // Get recent conversations for the authenticated user
        $conversations = Conversation::where(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })
            ->with([
                'userOne:id,name,username',
                'userTwo:id,name,username',
                'latestMessage.sender:id,name,username',
            ])
            ->orderBy('last_message_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($conversation) use ($userId) {
                $otherUser = $conversation->getOtherUser($userId);

                // Count unread messages in this conversation
                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'username' => $otherUser->username,
                        'is_online' => $otherUser->is_online ?? false,
                    ],
                    'latest_message' => $conversation->latestMessage->first(),
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        return Inertia::render('Home', [
            'conversations' => $conversations,
        ]);
    }
}
