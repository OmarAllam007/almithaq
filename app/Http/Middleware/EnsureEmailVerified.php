<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ! $user->hasVerifiedEmail() && ! $user->isAdmin()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
