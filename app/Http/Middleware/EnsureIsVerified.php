<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Redirect verified users (and admins) away from the verification page — they don't need it
            if (($user->isAdmin() || $user->is_verified) && $request->is('verification', 'verification/*')) {
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
