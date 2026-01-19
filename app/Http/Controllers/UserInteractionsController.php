<?php

namespace App\Http\Controllers;

use App\Services\UserInteractionService;
use Inertia\Inertia;

class UserInteractionsController extends Controller
{
    public function whoLikedMe(UserInteractionService $userInteractionService)
    {
        $users = $userInteractionService->whoLikedMe();

        return Inertia::render('UserInteractions/WhoLikedMe', [
            'users' => $users,
        ]);
    }

    public function whoVisitedMe(UserInteractionService $userInteractionService)
    {
        $users = $userInteractionService->whoVisitedMe();

        return Inertia::render('UserInteractions/WhoVisitedMe', [
            'users' => $users,
        ]);
    }

    public function myInteractions(UserInteractionService $userInteractionService)
    {
        $tab = request()->get('tab', 'favorites');

        if ($tab === 'ignored') {
            $users = $userInteractionService->myIgnoredUsers();
        } else {
            $users = $userInteractionService->myFavorites();
        }

        // Append the tab parameter to pagination links
        $users->appends(['tab' => $tab]);

        return Inertia::render('UserInteractions/MyInteractions', [
            'users' => $users,
            'activeTab' => $tab,
        ]);
    }
}
