<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\ImageRequest;
// use App\Models\Enums\SubscriptionStatus;
// use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class NewMembersController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $oppositeType = $currentUser->registration_type === 1 ? 2 : 1;

        $query = User::query()
            ->select([
                'id',
                'username',
                'age',
                'nationality',
                'residence',
                'marriage_status',
                'marriage_type',
                'registration_type',
                'last_seen_at',
            ])
            ->with('mainProfileImage')
            ->where('id', '!=', $currentUser->id)
            ->where('registration_type', $oppositeType)
            ->orderBy('created_at', 'desc');

        // Apply nationality filter
        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        // Apply residence filter
        if ($request->filled('residence')) {
            $query->where('residence', $request->residence);
        }

        // Paginate results
        $paginator = $query->paginate(40);
        $userIds = $paginator->pluck('id')->all();

        // Batch: online status (one Redis pipeline instead of N zscore calls)
        $minTimestamp = now()->subMinutes(10)->timestamp;
        $scores = Redis::pipeline(function ($pipe) use ($userIds) {
            foreach ($userIds as $id) {
                $pipe->zscore('online_users', $id);
            }
        });
        $onlineMap = array_combine($userIds, $scores);

        // Batch: favorited / ignored / viewable IDs (one query each)
        $favoritedIds = [];
        $ignoredIds = [];
        $viewableIds = [];
        if ($currentUser) {
            $favoritedIds = DB::table('favorites')
                ->where('user_id', $currentUser->id)
                ->whereIn('favorited_user_id', $userIds)
                ->pluck('favorited_user_id')
                ->flip()
                ->all();

            $ignoredIds = DB::table('ignores')
                ->where('user_id', $currentUser->id)
                ->whereIn('ignored_user_id', $userIds)
                ->pluck('ignored_user_id')
                ->flip()
                ->all();

            $viewableIds = ImageRequest::approvedViewersOf($currentUser->id, $userIds);
        }

        // TODO: activate when subscriptions go live
        // $subscriberIds = Subscription::whereIn('user_id', $userIds)
        //     ->where('status', SubscriptionStatus::ACTIVE)
        //     ->where('expires_at', '>', now())
        //     ->pluck('user_id')
        //     ->flip()
        //     ->all();

        $users = $paginator->through(function ($user) use ($onlineMap, $favoritedIds, $ignoredIds, $viewableIds, $minTimestamp) {
            $score = $onlineMap[$user->id] ?? null;
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => $user->nationality,
                'residence' => $user->residence,
                'marriage_status' => $user->marriage_status,
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $score !== null && $score >= $minTimestamp,
                'is_favorited' => isset($favoritedIds[$user->id]),
                'is_ignored' => isset($ignoredIds[$user->id]),
                'is_verified' => (bool) $user->is_verified,
                'is_subscriber' => false, // TODO: activate when subscriptions go live
                'registration_type' => $user->registration_type,
                'marriage_status_label' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'marriage_type_label' => MarriageType::tryFrom($user->marriage_type)?->label(),
            ];
        });

        // Get all countries for filters
        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        return Inertia::render('NewMembers/Index', [
            'users' => $users,
            'countries' => $countries,
            'filters' => [
                'nationality' => $request->nationality,
                'residence' => $request->residence,
            ],
        ]);
    }
}
