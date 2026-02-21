<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
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
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Models\ProfileVisit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $enumToOptions = fn (string $enumClass) => collect($enumClass::cases())->map(fn ($case) => [
            'value' => (string) $case->value,
            'label' => $case->label(),
        ])->toArray();

        return Inertia::render('Profile/ProfileIndex', [
            'countries' => Country::orderBy('name')->select(['id', 'name', 'ar_name', 'flag'])->get(),
            'marriage_types' => $enumToOptions(MarriageType::class),
            'marriage_statuses' => $enumToOptions(MarriageStatus::class),
            'skin_colors' => $enumToOptions(SkinColor::class),
            'body_shapes' => $enumToOptions(BodyShape::class),
            'devotions' => $enumToOptions(DevotionType::class),
            'prayer_commitments' => $enumToOptions(PrayerType::class),
            'yes_no_options' => [
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ],
            'education_levels' => $enumToOptions(EducationLevel::class),
            'financial_statuses' => $enumToOptions(FinancialStatus::class),
            'health_statuses' => $enumToOptions(HealthStatusType::class),
            'fields_of_work' => $enumToOptions(FieldOfWork::class),
            'delete_account_reasons' => $enumToOptions(DeleteAccountReason::class),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function show(User $user): JsonResponse
    {

        if (auth()->check() && auth()->id() !== $user->id) {
            ProfileVisit::create([
                'visitor_id' => auth()->id(),
                'visited_user_id' => $user->id,
            ]);
        }

        $userData = UserProfileResource::make($user);

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
