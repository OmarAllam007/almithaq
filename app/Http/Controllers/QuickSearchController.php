<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\ImageRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class QuickSearchController extends Controller
{
    public function index(Request $request)
    {
        syncLangFiles(['quick_search', 'home']);

        $currentUser = Auth::user();
        $oppositeType = $currentUser->registration_type === 1 ? 2 : 1;
        $isBrowsingMaleProfiles = $currentUser->registration_type === 2;

        $query = User::query()
            ->select(['id', 'username', 'age', 'nationality', 'residence', 'city', 'marriage_status', 'marriage_type', 'registration_type', 'last_seen_at'])
            ->with('mainProfileImage')
            ->where('id', '!=', $currentUser->id)
            ->where('registration_type', $oppositeType);

        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        if ($request->filled('residence')) {
            $query->where('residence', $request->residence);
        }

        if ($request->filled('marriage_status')) {
            $query->where('marriage_status', $request->marriage_status);
        }

        if ($request->filled('age_min')) {
            $query->where('age', '>=', (int) $request->age_min);
        }

        if ($request->filled('age_max')) {
            $query->where('age', '<=', (int) $request->age_max);
        }

        if ($request->filled('city_id')) {
            $query->where('city', (int) $request->city_id);
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $userIds = $paginator->pluck('id')->all();
        $minTimestamp = now()->subMinutes(10)->timestamp;

        $scores = Redis::pipeline(function ($pipe) use ($userIds) {
            foreach ($userIds as $id) {
                $pipe->zscore('online_users', $id);
            }
        });
        $onlineMap = array_combine($userIds ?: [], $scores ?: []);

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

        $users = $paginator->through(function ($user) use ($onlineMap, $favoritedIds, $ignoredIds, $viewableIds, $minTimestamp, $isBrowsingMaleProfiles) {
            $score = $onlineMap[$user->id] ?? null;
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
                'is_online' => $score !== null && $score >= $minTimestamp,
                'is_favorited' => isset($favoritedIds[$user->id]),
                'is_ignored' => isset($ignoredIds[$user->id]),
                'is_verified' => (bool) $user->is_verified,
                'is_subscriber' => false,
                'registration_type' => $user->registration_type,
                'marriage_status_label' => MarriageStatus::tryFrom($user->marriage_status)?->labelForGender($isBrowsingMaleProfiles),
                'marriage_type_label' => MarriageType::tryFrom($user->marriage_type)?->label(),
            ];
        });

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        $marriageStatusLabel = null;
        if ($request->filled('marriage_status')) {
            $marriageStatusLabel = MarriageStatus::tryFrom((int) $request->marriage_status)
                ?->labelForGender($isBrowsingMaleProfiles);
        }

        $cityFilter = $request->filled('city_id')
            ? City::find((int) $request->city_id)?->only(['id', 'name', 'ar_name'])
            : null;

        return Inertia::render('QuickSearch/Index', [
            'users' => $users,
            'countries' => $countries,
            'filters' => [
                'nationality' => $request->filled('nationality') ? (int) $request->nationality : null,
                'residence' => $request->filled('residence') ? (int) $request->residence : null,
                'city' => $cityFilter,
                'marriage_status' => $request->filled('marriage_status') ? (int) $request->marriage_status : null,
                'marriage_status_label' => $marriageStatusLabel,
                'age_min' => $request->input('age_min', 18),
                'age_max' => $request->input('age_max', 60),
            ],
        ]);
    }
}
