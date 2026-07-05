<?php

namespace App\Http\Controllers;

use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\ImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherUser($user->id);

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'username' => $otherUser->username,
                        'is_online' => $otherUser->isOnline(),
                        'last_seen_at' => $otherUser->last_seen_at,
                    ],
                    'latest_message' => $conversation->latestMessage->first(),
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->hasUser($user->id)) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()->with('sender')->get();

        // Mark messages as read
        $updated = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            broadcast(new MessagesRead($conversation->id, $user->id))->toOthers();
        }

        $otherUser = $conversation->getOtherUser($user->id);
        $otherUser->load('mainProfileImage');

        $canViewImages = ImageRequest::where('requester_id', $user->id)
            ->where('requested_user_id', $otherUser->id)
            ->value('status') === 'approved';

        $mainImage = $otherUser->mainProfileImage->first();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'username' => $otherUser->username,
                    'registration_type' => $otherUser->registration_type,
                    'age' => $otherUser->age,
                    'nationality' => $otherUser->nationality,
                    'residence' => $otherUser->residence,
                    'is_online' => $otherUser->isOnline(),
                    'last_seen_at' => $otherUser->last_seen_at,
                    'profile_image' => $canViewImages
                        ? ($mainImage?->original_url ?? $mainImage?->thumbnail_url)
                        : $mainImage?->thumbnail_url,
                    'can_view_images' => $canViewImages,
                    'is_ignored' => $otherUser->isIgnored($user->id),
                ],
            ],
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
        ]);

        $user = $request->user();
        $recipientId = $request->input('recipient_id');

        if ($user->id === $recipientId) {
            return response()->json(['error' => 'Cannot start conversation with yourself'], 422);
        }

        // Check if conversation already exists
        $existingConversation = $user->getConversationWith($recipientId);

        if ($existingConversation) {
            return response()->json(['conversation_id' => $existingConversation->id]);
        }

        // Create new conversation
        $conversation = Conversation::create([
            'user_one_id' => $user->id,
            'user_two_id' => $recipientId,
        ]);

        return response()->json(['conversation_id' => $conversation->id], 201);
    }

    public function destroy(Request $request, Conversation $conversation): void
    {
        $user = $request->user();

        if (! $conversation->hasUser($user->id)) {
            abort(403, 'Unauthorized');
        }

        $conversation->delete();

    }

    public function bulkDestroy(Request $request): void
    {
        $request->validate([
            'conversation_ids' => ['required', 'array'],
            'conversation_ids.*' => ['required', 'integer', 'exists:conversations,id'],
        ]);

        $user = $request->user();
        $conversationIds = $request->input('conversation_ids');

        Conversation::whereIn('id', $conversationIds)
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->delete();

        //        return response()->json([
        //            'success' => true,
        //            'message' => "{$deleted} conversation(s) deleted successfully",
        //            'deleted_count' => $deleted,
        //        ]);
    }
}
