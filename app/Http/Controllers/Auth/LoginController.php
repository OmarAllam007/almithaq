<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginController extends Controller
{
    //
    public function showLoginForm()
    {
        syncLangFiles(['login']);

        return Inertia::render('Auth/Login', []);
    }

    public function store(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();
        if (! $user) {
            return back()->withErrors([
                'username' => 'The provided credentials does not match our records.',
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided credentials does not match our records.',
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    public function logout()
    {
        \auth()->logout();

        return redirect()->route('login');
    }
}
