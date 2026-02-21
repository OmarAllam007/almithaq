<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserProfileResource;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = Session::get('locale', App::getLocale());
        App::setLocale($locale);

        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => $user ? [
                'user' => UserProfileResource::make($user)->toArray($request),
                'profile_image' => $user?->mainProfileImage()?->first()?->thumbnail_url,
            ] : null,
            'locale' => $locale,
            'lang' => $this->getLanguageData($locale),
        ];
    }

    private function getLanguageData($locale)
    {
        $langPath = resource_path("lang/{$locale}");

        $langData = [];

        if (is_dir($langPath)) {
            $files = glob($langPath . '/*.php');
            foreach ($files as $file) {
                $key = basename($file, '.php');
                $langData[$key] = require $file;
            }
        }

        return $langData;
    }
}
