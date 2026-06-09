<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Country;
use App\Models\Enums\MarriageStatus;
use App\Models\Favorite;
use App\Models\Message;
use App\Models\ProfileVisit;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        syncLangFiles(['home']);

        $user = Auth::user();
        $userId = $user->id;

        $profileViewsCount = ProfileVisit::where('visited_user_id', $userId)->count();
        $likesCount = Favorite::where('favorited_user_id', $userId)->count();
        $conversationsCount = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->count();
        $daysSinceJoined = (int) $user->created_at->diffInDays(now());

        $stats = [
            'profile_views' => $profileViewsCount,
            'likes' => $likesCount,
            'conversations' => $conversationsCount,
            'days_since_joined' => $daysSinceJoined,
        ];

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

                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();

                $profileImage = $otherUser->mainProfileImage()->first();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'username' => $otherUser->username,
                        'is_online' => $otherUser->isOnline(),
                        'profile_image' => $profileImage?->thumbnail_url,
                    ],
                    'latest_message' => $conversation->latestMessage->first(),
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        $notifications = UserNotification::where('user_id', $userId)
            ->with(['actor:id,name,username'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                $actorImage = $notification->actor?->mainProfileImage()->first();

                return [
                    'id' => $notification->id,
                    'type' => $notification->type->value,
                    'data' => $notification->data,
                    'is_read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at,
                    'actor' => $notification->actor ? [
                        'id' => $notification->actor->id,
                        'name' => $notification->actor->name,
                        'username' => $notification->actor->username,
                        'profile_image' => $actorImage?->thumbnail_url,
                    ] : null,
                ];
            });

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        // Labels represent the profiles being searched (opposite gender of current user)
        $isBrowsingMaleProfiles = $user->registration_type === 2;
        $availableStatuses = [MarriageStatus::SINGLE, MarriageStatus::DIVORCED, MarriageStatus::WIDOWED];

        $marriageStatuses = collect($availableStatuses)->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->labelForGender($isBrowsingMaleProfiles),
        ])->values();

        return Inertia::render('Home', [
            'stats' => $stats,
            'conversations' => $conversations,
            'notifications' => $notifications,
            'countries' => $countries,
            'marriageStatuses' => $marriageStatuses,
        ]);
    }
}
