<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteProfileRequest;
use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FieldOfWork;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\HealthStatusType;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\MonthlyIncomeType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\RegistrationType;
use App\Models\Enums\SkinColor;
use Inertia\Inertia;

class CompleteProfileController extends Controller
{
    private function enumToOptions(string $enumClass): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], $enumClass::cases());
    }

    public function show()
    {
        $user = auth()->user();

        if ($user->profile_completed) {
            return redirect()->route('home');
        }

        $countries = Country::orderBy('name')->select(['id', 'name', 'ar_name', 'flag'])->get();

        // Marriage types filtered by registration type
        $husbandMarriageTypes = [MarriageType::FIRST_WIFE, MarriageType::SECOND_WIFE];
        $wifeMarriageTypes = [MarriageType::ONLY_ONE_WIFE, MarriageType::ACCEPT_POLYGAMY];

        $marriageTypes = array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            $user->registration_type === RegistrationType::AS_HUSBAND ? $husbandMarriageTypes : $wifeMarriageTypes
        );

        // Marriage statuses filtered by registration type
        $husbandMarriageStatuses = [MarriageStatus::SINGLE, MarriageStatus::DIVORCED, MarriageStatus::WIDOWED, MarriageStatus::MARRIED];
        $wifeMarriageStatuses = [MarriageStatus::SINGLE, MarriageStatus::DIVORCED, MarriageStatus::WIDOWED];

        $marriageStatuses = array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            $user->registration_type === RegistrationType::AS_HUSBAND ? $husbandMarriageStatuses : $wifeMarriageStatuses
        );

        return Inertia::render('Auth/CompleteProfile', [
            'countries' => $countries,
            'marriage_types' => $marriageTypes,
            'marriage_statuses' => $marriageStatuses,
            'devotions' => $this->enumToOptions(DevotionType::class),
            'prayer_commitments' => $this->enumToOptions(PrayerType::class),
            'yes_no_list' => [
                ['value' => 1, 'label' => 'Yes'],
                ['value' => 2, 'label' => 'No'],
            ],
            'education_levels' => $this->enumToOptions(EducationLevel::class),
            'financial_statuses' => $this->enumToOptions(FinancialStatus::class),
            'fields_of_work' => $this->enumToOptions(FieldOfWork::class),
            'skin_colors' => $this->enumToOptions(SkinColor::class),
            'body_shapes' => $this->enumToOptions(BodyShape::class),
            'health_statuses' => $this->enumToOptions(HealthStatusType::class),
            'monthly_incomes' => $this->enumToOptions(MonthlyIncomeType::class),
            'user' => $user,
        ]);
    }

    public function store(CompleteProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->profile_completed) {
            return redirect()->route('home');
        }

        $user->update(array_merge(
            $request->validated(),
            ['profile_completed' => true]
        ));

        return redirect()->route('home');
    }
}
