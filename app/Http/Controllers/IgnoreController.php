<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IgnoreController extends Controller
{
    public function toggle(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['error' => 'Cannot ignore yourself'], 422);
        }

        $isIgnored = $currentUser->ignores()->where('ignored_user_id', $user->id)->exists();

        if ($isIgnored) {
            $currentUser->ignores()->detach($user->id);
            $message = 'User unignored';
        } else {
            $currentUser->ignores()->attach($user->id);
            $currentUser->favorites()->detach($user->id);
            $message = 'User ignored';
        }

        return response()->json([
            'success' => true,
            'is_ignored' => ! $isIgnored,
            'message' => $message,
        ]);
    }
}
