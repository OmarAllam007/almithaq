<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Requests\RegisterRequest;
use App\Models\Enums\RegistrationType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showSignupForm()
    {
        return Inertia::render('Auth/Signup');
    }

    public function store(RegisterRequest $request)
    {
        $utm = $request->session()->pull(CaptureUtmParameters::SESSION_KEY, []);

        $user = DB::transaction(function () use ($request, $utm): User {
            $user = User::create(array_merge(
                $request->validated(),
                [
                    'registration_type' => $request->registration_type === 'husband' ? RegistrationType::AS_HUSBAND : RegistrationType::AS_WIFE,
                    'profile_completed' => false,
                ]
            ));

            if ($utm !== []) {
                $user->acquisition()->create($utm);
            }

            return $user;
        });

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        return redirect()->route('complete-profile');
    }
}
