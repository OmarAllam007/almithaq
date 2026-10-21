<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Payment;
use App\Models\ProfileImage;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', [
            'stats' => [
                'users' => User::count(),
                'activeUsers' => User::where('is_locked', false)->count(),
                'pendingVerifications' => User::whereNotNull('verification_video_path')->count(),
                'pendingImages' => ProfileImage::where('is_approved', false)->count(),
                'payments' => Payment::count(),
                'countries' => Country::count(),
                'cities' => City::count(),
            ],
            'recentUsers' => User::latest()->limit(6)->get(['id', 'name', 'username', 'email', 'is_locked', 'is_verified', 'created_at']),
        ]);
    }
}
