<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{

    public function handle(Request $request, Closure $next)
    {
        // Get locale from session, user preference, or default to 'en'
        $locale = Session::get('locale');

        // If no session locale, check user's saved preference
        if (!$locale && auth()->check()) {
            $locale = auth()->user()->language ?? 'en';
        }

        // If still no locale, default to 'en'
        if (!$locale) {
            $locale = 'en';
        }

        // Validate locale
        $validLocales = ['en', 'ar'];
        if (!in_array($locale, $validLocales)) {
            $locale = 'en';
        }

        // Set the application locale
        App::setLocale($locale);

        return $next($request);
    }
}
