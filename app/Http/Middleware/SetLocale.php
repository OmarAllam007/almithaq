<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{

    public function handle(Request $request, Closure $next): mixed
    {
        $locale = Session::get('locale');

        if (!$locale && auth()->check()) {
            $locale = auth()->user()->language ?? 'ar';
        }

        if (!$locale) {
            $locale = 'ar';
            Session::put('locale', $locale);
        }

        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
