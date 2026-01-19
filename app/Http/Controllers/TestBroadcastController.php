<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;

class TestBroadcastController extends Controller
{
    public function testBroadcast(): JsonResponse
    {
        // Get a test conversation
        $conversation = Conversation::with(['messages', 'userOne', 'userTwo'])->first();

        if (! $conversation) {
            return response()->json([
                'error' => 'No conversations found. Create a conversation first.',
            ], 404);
        }

        // Create a test message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->user_one_id,
            'message' => 'Test broadcast message at '.now()->toDateTimeString(),
            'is_read' => false,
        ]);

        $message->load('sender');

        // Broadcast the event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Test message broadcast successfully',
            'data' => [
                'conversation_id' => $conversation->id,
                'channel' => 'private-conversation.'.$conversation->id,
                'event' => 'MessageSent',
                'message' => $message,
                'receiver_id' => $conversation->user_two_id,
            ],
        ]);
    }

    public function echoTest(): JsonResponse
    {
        return response()->json([
            'broadcast_driver' => config('broadcasting.default'),
            'queue_driver' => config('queue.default'),
            'reverb_config' => [
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ],
        ]);
    }
}
