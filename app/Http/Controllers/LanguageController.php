<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $locale)
    {
        // Validate the locale
        $validLocales = ['en', 'ar'];

        if (!in_array($locale, $validLocales)) {
            $locale = 'en';
        }

        // Set the locale in session
        Session::put('locale', $locale);

        // Set the application locale
        App::setLocale($locale);

        // Store user's language preference if authenticated
        if (auth()->check()) {
//            auth()->user()->update(['language' => $locale]);
        }

        // Redirect back to the previous page or home
        return redirect()->back();
    }

    public function getCurrentLanguage()
    {
        return response()->json([
            'locale' => App::getLocale(),
            'session_locale' => Session::get('locale', 'en')
        ]);
    }
}
