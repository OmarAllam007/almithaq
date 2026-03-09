<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Enums\RegistrationType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showSignupForm()
    {
        return Inertia::render('Auth/Signup');
    }

    public function store(RegisterRequest $request)
    {
        $user = User::create(array_merge(
            $request->validated(),
            [
                'registration_type' => $request->registration_type === 'husband' ? RegistrationType::AS_HUSBAND : RegistrationType::AS_WIFE,
                'profile_completed' => false,
            ]
        ));

        Auth::login($user);

        return redirect()->route('complete-profile');
    }
}
