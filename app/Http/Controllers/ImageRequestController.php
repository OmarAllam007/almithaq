<?php

namespace App\Http\Controllers;

use App\Models\ImageRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ImageRequestController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(): InertiaResponse
    {
        $receivedRequests = auth()->user()
            ->receivedImageRequests()
            ->with(['requester.mainProfileImage'])
            ->latest()
            ->get()
            ->map(function (ImageRequest $imageRequest) {
                $requester = $imageRequest->requester;

                return [
                    'id' => $imageRequest->id,
                    'status' => $imageRequest->status,
                    'created_at' => $imageRequest->created_at,
                    'requester' => [
                        'id' => $requester->id,
                        'username' => $requester->username,
                        'age' => $requester->age,
                        'mainProfileImage' => $requester->mainProfileImage->first()?->thumbnail_url,
                        'is_online' => $requester->isOnline(),
                    ],
                ];
            });

        return Inertia::render('ImageRequests', [
            'requests' => $receivedRequests,
        ]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['error' => trans('image_requests.cannot_request_own')], 422);
        }

        $existing = ImageRequest::where('requester_id', $currentUser->id)
            ->where('requested_user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => trans('image_requests.request_already_exists'),
                'status' => $existing->status,
            ], 422);
        }

        ImageRequest::create([
            'requester_id' => $currentUser->id,
            'requested_user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->notificationService->notifyImageRequest($user->id, $currentUser->id);

        return response()->json([
            'success' => true,
            'status' => 'pending',
        ]);
    }

    public function approve(Request $request, ImageRequest $imageRequest): JsonResponse
    {
        if ($imageRequest->requested_user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $imageRequest->isPending()) {
            return response()->json(['error' => trans('image_requests.not_pending')], 422);
        }

        $imageRequest->update(['status' => 'approved']);

        $this->notificationService->notifyImageRequestApproved(
            $imageRequest->requester_id,
            $request->user()->id
        );

        return response()->json(['success' => true, 'status' => 'approved']);
    }

    public function reject(Request $request, ImageRequest $imageRequest): JsonResponse
    {
        if ($imageRequest->requested_user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $imageRequest->isPending()) {
            return response()->json(['error' => trans('image_requests.not_pending')], 422);
        }

        $imageRequest->update(['status' => 'rejected']);

        $this->notificationService->notifyImageRequestRejected(
            $imageRequest->requester_id,
            $request->user()->id
        );

        return response()->json(['success' => true, 'status' => 'rejected']);
    }

    public function destroy(Request $request, ImageRequest $imageRequest): JsonResponse
    {
        if ($imageRequest->requested_user_id !== $request->user()->id) {
            abort(403);
        }

        $imageRequest->delete();

        return response()->json(['success' => true]);
    }
}
