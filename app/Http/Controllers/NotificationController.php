<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->with('actor:id,username')
            ->with('actor.mainProfileImage')
            ->latest()
            ->paginate(20);

        $notifications->through(fn (UserNotification $notification) => [
            'id' => $notification->id,
            'type' => $notification->type->value,
            'label' => $notification->type->label(),
            'actor' => $notification->actor ? [
                'id' => $notification->actor->id,
                'username' => $notification->actor->username,
                'profile_image' => $notification->actor->mainProfileImage()->first()->image_path ?? null,
            ] : null,
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at->diffForHumans(),
        ]);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user()->id);

        return response()->json(['success' => true]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);

        return response()->json(['count' => $count]);
    }
}
