<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['error' => 'Cannot favorite yourself'], 422);
        }

        $isFavorited = $currentUser->favorites()->where('favorited_user_id', $user->id)->exists();

        if ($isFavorited) {
            $currentUser->favorites()->detach($user->id);
            $message = 'User removed from favorites';
        } else {
            $currentUser->favorites()->attach($user->id);
            $currentUser->ignores()->detach($user->id);
            $message = 'User added to favorites';
        }

        return response()->json([
            'success' => true,
            'is_favorited' => ! $isFavorited,
            'message' => $message,
        ]);
    }
}
