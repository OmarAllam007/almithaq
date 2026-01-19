<?php

namespace App\Services;

use App\Models\User;

class UserInteractionService
{
    public function whoLikedMe()
    {
        return auth()->user()
            ->favoritedBy()
            ->with('mainProfileImage')
            ->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'age' => $user->age,
                    'nationality' => $user->nationality,
                    'profile_image' => $user->mainProfileImage()->first()->image_path ?? null,
                    'created_at' => $user->pivot->created_at->diffForHumans(), // I need favourite created_at,
                    'is_favourite' => $user->isFavoritedBy(auth()->id()),
                    'is_ignored' => $user->isIgnored(auth()->id()),
                ];
            });
    }

    public function whoVisitedMe()
    {
        $visitedUserId = auth()->id();

        // Get unique visitors with their latest visit date
        return User::query()
            ->join('profile_visits', 'users.id', '=', 'profile_visits.visitor_id')
            ->where('profile_visits.visited_user_id', $visitedUserId)
            ->select('users.*', \DB::raw('MAX(profile_visits.created_at) as latest_visit'))
            ->groupBy('users.id')
            ->orderByDesc('latest_visit')
            ->with('mainProfileImage')
            ->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'age' => $user->age,
                    'nationality' => $user->nationality,
                    'marriage_status' => $user->marriage_status,
                    'mainProfileImage' => $user->mainProfileImage()->first()->image_path ?? null,
                    'created_at' => \Carbon\Carbon::parse($user->latest_visit)->diffForHumans(),
                    'is_favourite' => $user->isFavoritedBy(auth()->id()),
                    'is_ignored' => $user->isIgnored(auth()->id()),
                ];
            });
    }

    public function myFavorites()
    {
        return auth()->user()
            ->favorites()
            ->with('mainProfileImage')
            ->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'age' => $user->age,
                    'nationality' => $user->nationality,
                    'marriage_status' => $user->marriage_status,
                    'mainProfileImage' => $user->mainProfileImage()->first()->image_path ?? null,
                    'created_at' => $user->pivot->created_at->diffForHumans(),
                ];
            });
    }

    public function myIgnoredUsers()
    {
        return auth()->user()
            ->ignores()
            ->with('mainProfileImage')
            ->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'age' => $user->age,
                    'nationality' => $user->nationality,
                    'marriage_status' => $user->marriage_status,
                    'mainProfileImage' => $user->mainProfileImage()->first()->image_path ?? null,
                    'created_at' => $user->pivot->created_at->diffForHumans(),
                ];
            });
    }
}
