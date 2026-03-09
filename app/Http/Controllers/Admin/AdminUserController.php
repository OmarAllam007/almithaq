<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->select([
                'id',
                'name',
                'username',
                'email',
                'phone_number',
                'nationality',
                'registration_type',
                'is_active',
                'is_verified',
                'last_seen_at',
                'created_at',
            ])
            ->orderBy('created_at', 'desc');

        // Apply nationality filter
        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        // Apply registration_type filter
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Apply is_active filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Apply is_verified filter
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        // Search by name, username, email or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'nationality' => $user->nationality,
                'registration_type' => $user->registration_type,
                'is_active' => (bool) $user->is_active,
                'is_verified' => (bool) $user->is_verified,
                'is_online' => $user->isOnline(),
                'created_at' => $user->created_at?->format('Y-m-d H:i'),
            ];
        });

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'countries' => $countries,
            'filters' => [
                'nationality' => $request->nationality,
                'registration_type' => $request->registration_type,
                'is_active' => $request->is_active,
                'is_verified' => $request->is_verified,
                'search' => $request->search,
            ],
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);

        return redirect()->back()->with('success', 'User unverified successfully.');
    }
}
