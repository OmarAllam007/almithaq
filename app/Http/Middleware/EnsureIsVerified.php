<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsVerified
{
    /**
     * Routes that should be accessible even without verification.
     */
    protected array $except = [
        'verification',
        'verification/*',
        'logout',
        'language',
        'language/*',
        'complete-profile',
        'complete-profile/*',
    ];

    /**
     * Handle an incoming request.
     *
     * Redirects non-verified, non-admin users to the verification upload page.
     * Allows access only to the upload page and logout until verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Admins bypass verification

            if ($user->isAdmin()) {
                return $next($request);
            }

            // If user is already verified, prevent access to verification page
            if ($user->is_verified && $request->is('verification', 'verification/*')) {
                return redirect()->route('home');
            }

            // If user is NOT verified
            if (!$user->is_verified) {
                // Allow access to excepted routes
                foreach ($this->except as $pattern) {
                    if ($request->is($pattern)) {
                        return $next($request);
                    }
                }

                // Block everything else — redirect to verification page
                return redirect()->route('verification.show');
            }
        }

        return $next($request);
    }
}
