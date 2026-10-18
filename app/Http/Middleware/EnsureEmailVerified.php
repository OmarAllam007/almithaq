<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Guards the actions that reach another member. Browsing and completing a
     * profile stay open so a new signup can see the platform first.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ! $user->hasVerifiedEmail() && ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'reason' => 'email_unverified',
                    'message' => __('email_verification.required_to_message'),
                ], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
