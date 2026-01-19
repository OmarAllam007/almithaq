<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewMembersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->select([
                'id',
                'username',
                'age',
                'nationality',
                'residence',
                'marriage_status',
                'last_seen_at',
            ])
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
        $users = $query->paginate(20)->through(function ($user) use ($request) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => $user->nationality,
                'residence' => $user->residence,
                'marriage_status' => $user->marriage_status,
                'is_online' => $user->isOnline(),
                'is_favorited' => $request->user() ? $user->isFavoritedBy($request->user()->id) : false,
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
