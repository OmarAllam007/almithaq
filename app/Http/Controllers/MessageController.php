<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->hasUser($user->id)) {
            abort(403, 'Unauthorized');
        }
        //        dd('asdasd');
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        // Send notification only if the last message was still unread (not actively chatting)
        $receiverId = $conversation->getOtherUserId($user->id);
        $hasUnreadMessages = $conversation->messages()
            ->where('sender_id', $user->id)
            ->where('id', '!=', $message->id)
            ->where('is_read', false)
            ->exists();

        if (! $hasUnreadMessages && $receiverId) {
            $this->notificationService->notifyNewMessage($receiverId, $user->id, $conversation->id);
        }

        return response()->json([
            'message' => $message->load('sender'),
            'status' => 'Message sent!',
        ], 201);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->hasUser($user->id)) {
            abort(403, 'Unauthorized');
        }

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function typing(Conversation $conversation)
    {
        broadcast(new UserTyping($conversation->id, auth()->id()))->toOthers();

        return response()->json([
            'status' => 'typing broadcasted!',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $conversationIds = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        //        return response()->json(['success' => true, 'message' => 'All messages marked as read']);
    }
}
