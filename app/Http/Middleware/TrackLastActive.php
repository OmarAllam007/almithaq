<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Redis::zadd('online_users', now()->timestamp, Auth::id());
        }

        return $next($request);
    }
}
