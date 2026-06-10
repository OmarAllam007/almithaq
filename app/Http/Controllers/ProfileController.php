<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\City;
use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DeleteAccountReason;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FieldOfWork;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\HealthStatusType;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\MonthlyIncomeType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Models\ImageRequest;
use App\Models\ProfileImage;
use App\Models\ProfileVisit;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(): Response
    {
        $enumToOptions = fn (string $enumClass) => collect($enumClass::cases())->map(fn ($case) => [
            'value' => (string) $case->value,
            'label' => $case->label(),
        ])->toArray();

        $locale = app()->getLocale();

        return Inertia::render('Profile/ProfileIndex', [
            'countries' => Country::orderBy('name')->select(['id', 'name', 'ar_name', 'flag'])->get(),
            'cities' => City::select(['id', 'country_id', 'name', 'ar_name'])
                ->orderBy($locale === 'ar' ? 'ar_name' : 'name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'country_id' => $c->country_id,
                    'name' => trim($locale === 'ar' ? ($c->ar_name ?: $c->name) : $c->name),
                ]),
            'marriage_types' => $enumToOptions(MarriageType::class),
            'marriage_statuses' => $enumToOptions(MarriageStatus::class),
            'skin_colors' => $enumToOptions(SkinColor::class),
            'body_shapes' => $enumToOptions(BodyShape::class),
            'devotions' => $enumToOptions(DevotionType::class),
            'prayer_commitments' => $enumToOptions(PrayerType::class),
            'yes_no_options' => [
                ['value' => '1', 'label' => trans('enums.yes')],
                ['value' => '0', 'label' => trans('enums.no')],
            ],
            'education_levels' => $enumToOptions(EducationLevel::class),
            'financial_statuses' => $enumToOptions(FinancialStatus::class),
            'health_statuses' => $enumToOptions(HealthStatusType::class),
            'fields_of_work' => $enumToOptions(FieldOfWork::class),
            'monthly_incomes' => $enumToOptions(MonthlyIncomeType::class),
            'delete_account_reasons' => $enumToOptions(DeleteAccountReason::class),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function chatInfo(User $user): JsonResponse
    {
        $user->load('mainProfileImage');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'is_online' => $user->isOnline(),
            'last_seen_at' => $user->last_seen_at,
            'profile_image' => $user->mainProfileImage->first()?->thumbnail_url,
            'is_ignored' => $user->isIgnored(auth()->id()),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $currentUserId = auth()->id();
        $isOwner = $currentUserId === $user->id;

        if (auth()->check() && ! $isOwner) {
            ProfileVisit::create([
                'visitor_id' => $currentUserId,
                'visited_user_id' => $user->id,
            ]);
            $this->notificationService->notifyProfileVisit($user->id, $currentUserId);
        }

        $imageRequestStatus = $isOwner ? null : ImageRequest::where('requester_id', $currentUserId)
            ->where('requested_user_id', $user->id)
            ->value('status');

        $canViewImages = $isOwner || $imageRequestStatus === 'approved';

        $galleryImages = $user->profileImages()
            ->ordered()
            ->get()
            ->map(fn (ProfileImage $image) => [
                'id' => $image->id,
                'thumbnail_url' => $image->thumbnail_url,
                'original_url' => $canViewImages ? $image->original_url : null,
                'is_main' => $image->is_main,
            ]);

        $mainImage = $user->mainProfileImage->first();

        $userData = array_merge(
            UserProfileResource::make($user)->toArray(request()),
            [
                'image_request_status' => $imageRequestStatus,
                'can_view_images' => $canViewImages,
                'gallery_images' => $galleryImages,
                'mainProfileImage' => $canViewImages
                    ? ($mainImage?->original_url ?? $mainImage?->thumbnail_url)
                    : $mainImage?->thumbnail_url,
            ]
        );

        return response()->json(['user' => $userData]);
    }

    public function loginLocation(User $user): JsonResponse
    {
        $lastLogin = $user->getLastLoginLog();

        if (! $lastLogin) {
            return response()->json([
                'country' => null,
                'city' => null,
            ]);
        }

        return response()->json([
            'country' => $lastLogin->country,
            'city' => $lastLogin->city,
        ]);
    }
}
