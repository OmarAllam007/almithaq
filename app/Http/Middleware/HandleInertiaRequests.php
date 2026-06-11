<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserProfileResource;
use App\Models\Conversation;
use App\Models\ImageRequest;
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
                'user' => array_merge(UserProfileResource::make($user)->toArray($request), [
                    'is_admin' => $user->isAdmin(),
                    'is_verified' => $user->isVerified(),
                    'has_active_subscription' => $user->hasActiveSubscription(),
                ]),
                'profile_image' => $user?->mainProfileImage()?->first()?->original_url,
            ] : null,
            'locale' => $locale,
            'lang' => $this->getLanguageData($locale),
            'adsense_client' => config('services.google_adsense.client'),
            'unread_conversations' => $user ? Conversation::query()
                ->where(function ($q) use ($user) {
                    $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
                })
                ->whereHas('messages', function ($q) use ($user) {
                    $q->where('is_read', false)->where('sender_id', '!=', $user->id);
                })
                ->count() : 0,
            'pending_image_requests' => $user
                ? ImageRequest::where('requested_user_id', $user->id)->where('status', 'pending')->count()
                : 0,
        ];
    }

    private function getLanguageData(string $locale): array
    {
        $langPath = resource_path("lang/{$locale}");

        $langData = [];

        if (is_dir($langPath)) {
            $files = glob($langPath.'/*.php');
            foreach ($files as $file) {
                $key = basename($file, '.php');
                $langData[$key] = require $file;
            }
        }

        return $langData;
    }
}
