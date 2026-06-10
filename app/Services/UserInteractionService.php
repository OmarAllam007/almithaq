<?php

namespace App\Services;

use App\Models\City;
use App\Models\Country;
use App\Models\Enums\MarriageStatus;
use App\Models\ImageRequest;
use App\Models\User;
use Carbon\Carbon;

class UserInteractionService
{
    public function whoLikedMe()
    {
        $authId = auth()->id();

        $paginator = auth()->user()
            ->favoritedBy()
            ->with('mainProfileImage')
            ->paginate(20);

        $userIds = $paginator->pluck('id')->all();
        $viewableIds = ImageRequest::approvedViewersOf($authId, $userIds);

        return $paginator->through(function (User $user) use ($authId, $viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => Country::find($user->nationality)?->only('id', 'name', 'ar_name', 'flag'),
                'residence' => Country::find($user->residence)?->only('id', 'name', 'ar_name', 'flag'),
                'city' => City::find($user->city)?->only('id', 'name', 'ar_name'),
                'marriage_status' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $user->isOnline(),
                'is_verified' => (bool) $user->is_verified,
                'liked_at' => $user->pivot->created_at->diffForHumans(),
                'is_favourite' => $user->isFavoritedBy($authId),
                'is_ignored' => $user->isIgnored($authId),
            ];
        });
    }

    public function whoVisitedMe()
    {
        $authId = auth()->id();

        $paginator = User::query()
            ->join('profile_visits', 'users.id', '=', 'profile_visits.visitor_id')
            ->where('profile_visits.visited_user_id', $authId)
            ->select('users.*', \DB::raw('MAX(profile_visits.created_at) as latest_visit'))
            ->groupBy('users.id')
            ->orderByDesc('latest_visit')
            ->with('mainProfileImage')
            ->paginate(20);

        $userIds = $paginator->pluck('id')->all();
        $viewableIds = ImageRequest::approvedViewersOf($authId, $userIds);

        return $paginator->through(function (User $user) use ($authId, $viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => Country::find($user->nationality)?->only('id', 'name', 'ar_name', 'flag'),
                'residence' => Country::find($user->residence)?->only('id', 'name', 'ar_name', 'flag'),
                'city' => City::find($user->city)?->only('id', 'name', 'ar_name'),
                'marriage_status' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $user->isOnline(),
                'is_verified' => (bool) $user->is_verified,
                'visited_at' => Carbon::parse($user->latest_visit)->diffForHumans(),
                'is_favourite' => $user->isFavoritedBy($authId),
                'is_ignored' => $user->isIgnored($authId),
            ];
        });
    }

    public function myFavorites()
    {
        $authId = auth()->id();

        $paginator = auth()->user()
            ->favorites()
            ->with('mainProfileImage')
            ->paginate(20);

        $userIds = $paginator->pluck('id')->all();
        $viewableIds = ImageRequest::approvedViewersOf($authId, $userIds);

        return $paginator->through(function (User $user) use ($authId, $viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => Country::find($user->nationality)?->only('id', 'name', 'ar_name', 'flag'),
                'residence' => Country::find($user->residence)?->only('id', 'name', 'ar_name', 'flag'),
                'city' => City::find($user->city)?->only('id', 'name', 'ar_name'),
                'marriage_status' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $user->isOnline(),
                'is_verified' => (bool) $user->is_verified,
                'is_favourite' => true,
                'is_ignored' => $user->isIgnored($authId),
                'created_at' => $user->pivot->created_at->diffForHumans(),
            ];
        });
    }

    public function myIgnoredUsers()
    {
        $authId = auth()->id();

        $paginator = auth()->user()
            ->ignores()
            ->with('mainProfileImage')
            ->paginate(20);

        $userIds = $paginator->pluck('id')->all();
        $viewableIds = ImageRequest::approvedViewersOf($authId, $userIds);

        return $paginator->through(function (User $user) use ($authId, $viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => Country::find($user->nationality)?->only('id', 'name', 'ar_name', 'flag'),
                'residence' => Country::find($user->residence)?->only('id', 'name', 'ar_name', 'flag'),
                'city' => City::find($user->city)?->only('id', 'name', 'ar_name'),
                'marriage_status' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $user->isOnline(),
                'is_verified' => (bool) $user->is_verified,
                'is_favourite' => $user->isFavoritedBy($authId),
                'is_ignored' => true,
                'created_at' => $user->pivot->created_at->diffForHumans(),
            ];
        });
    }
}
