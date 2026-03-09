<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Routes that should be accessible even with incomplete profile.
     */
    protected array $except = [
        'complete-profile',
        'complete-profile/*',
        'logout',
        'language',
        'language/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $profileCompleted = auth()->user()->profile_completed;

            if (! $profileCompleted) {
                foreach ($this->except as $pattern) {
                    if ($request->is($pattern)) {
                        return $next($request);
                    }
                }

                return redirect()->route('complete-profile');
            }

            if ($profileCompleted && $request->is('complete-profile', 'complete-profile/*')) {
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
