<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\ImageRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class OnlineMembersController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        // 1. Get total count of online users (last 10 minutes)
        $minTimestamp = now()->subMinutes(10)->timestamp;
        $onlineCount = Redis::zcount('online_users', $minTimestamp, '+inf');

        // 2. Fetch ALL online user IDs sorted by most recently active
        $allUserIds = Redis::zrevrange('online_users', 0, -1);

        // 3. Hydrate User Models with filters (show only opposite gender)
        $currentUser = Auth::user();
        $oppositeType = $currentUser->registration_type === 1 ? 2 : 1;

        $query = User::whereIn('id', $allUserIds)
            ->whereNot('id', Auth::id())
            ->where('registration_type', $oppositeType)
            ->with('mainProfileImage');

        // Apply filters
        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        if ($request->filled('residence')) {
            $query->where('residence', $request->residence);
        }

        $users = $query->get()
            ->sortBy(function ($user) use ($allUserIds) {
                return array_search($user->id, $allUserIds);
            })
            ->values();

        // 4. Manual pagination
        $page = $request->input('page', 1);
        $perPage = 15;
        $total = $users->count();

        $paginatedUsers = $users->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $paginatedUsers,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $pageUserIds = $paginatedUsers->pluck('id')->all();
        $viewableIds = ImageRequest::approvedViewersOf($currentUser->id, $pageUserIds);

        $mappedUsers = $paginatedUsers->map(function (User $user) use ($viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => $user->nationality,
                'residence' => $user->residence,
                'city' => City::find($user->city)?->only('id', 'name', 'ar_name'),
                'marriage_status' => $user->marriage_status,
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => true,
                'is_favorited' => $user->isFavoritedBy(Auth::id()),
                'is_ignored' => $user->isIgnored(Auth::id()),
                'is_verified' => (bool) $user->is_verified,
                'is_subscriber' => $user->hasActiveSubscription(),
                'registration_type' => $user->registration_type,
                'marriage_status_label' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'marriage_type_label' => MarriageType::tryFrom($user->marriage_type)?->label(),
            ];
        });

        $usersResponse = new LengthAwarePaginator(
            $mappedUsers,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('OnlineUsers/Index', [
            'users' => $usersResponse,
            'countries' => $countries,
            'totalOnline' => $onlineCount ? max(0, $onlineCount - 1) : 0,
            'filters' => [
                'nationality' => $request->nationality,
                'residence' => $request->residence,
            ],
        ]);
    }
}
