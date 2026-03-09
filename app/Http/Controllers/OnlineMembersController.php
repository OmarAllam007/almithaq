<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\Country;
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

        // 3. Hydrate User Models with filters
        $query = User::whereIn('id', $allUserIds)
            ->whereNot('id', Auth::id());

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

        return Inertia::render('OnlineUsers/Index', [
            'users' => UserProfileResource::collection($paginator),
            'countries' => $countries,
            'totalOnline' => $onlineCount ? max(0, $onlineCount - 1) : 0,
            'filters' => [
                'nationality' => $request->nationality,
                'residence' => $request->residence,
            ],
        ]);
    }
}
