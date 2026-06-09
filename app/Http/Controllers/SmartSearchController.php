<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Services\SmartSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SmartSearchController extends Controller
{
    public function index(SmartSearchService $searchService)
    {
        // dd($searchService);
        $cachedFilters = $searchService->getCachedFilters();
        $users = null;

        if ($cachedFilters) {
            $users = $searchService->search($cachedFilters);
        }

        $countries = Country::select(['id', 'name', 'ar_name'])->orderByDesc('is_arab')->orderBy('name')->get();

        return Inertia::render('SmartSearch/Index', [
            'users' => $users,
            'cachedFilters' => $cachedFilters,
            'filterOptions' => $this->getFilterOptions(),
            'countries' => $countries,
        ]);
    }

    public function search(Request $request, SmartSearchService $searchService)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('smart-search.index');
        }
        // dd($request->all());
        $filters = $request->only([
            'residence',
            'nationality',
            'marriage_status',
            'marriage_type',
            'age_min',
            'age_max',
            'body_shape',
            'skin_color',
            'weight_min',
            'weight_max',
            'height_min',
            'height_max',
            'education_level',
            'financial_status',
            'devotion',
            'prayer',
            'smoking',
        ]);

        // Remove empty values
        $filters = array_filter($filters, function ($value) {
            return ! empty($value) || $value === 0;
        });

        // Cache the filters
        $searchService->cacheFilters($filters);

        // Perform search
        $users = $searchService->search($filters);

        $countries = Country::select(['id', 'name', 'ar_name'])->orderByDesc('is_arab')->orderBy('name')->get();

        return Inertia::render('SmartSearch/Index', [
            'users' => $users,
            'cachedFilters' => $filters,
            'filterOptions' => $this->getFilterOptions(),
            'countries' => $countries,
        ]);
    }

    public function clearFilters(SmartSearchService $searchService)
    {
        $searchService->clearCachedFilters();

        return redirect()->route('smart-search.index');
    }

    private function getFilterOptions(): array
    {
        // Labels should match the profiles being searched (opposite gender of current user)
        $isBrowsingMaleProfiles = Auth::user()->registration_type === 2;

        $availableStatuses = [MarriageStatus::SINGLE, MarriageStatus::DIVORCED, MarriageStatus::WIDOWED];

        $availableMarriageTypes = $isBrowsingMaleProfiles
            ? [MarriageType::FIRST_WIFE, MarriageType::SECOND_WIFE, MarriageType::ONLY_ONE_WIFE, MarriageType::ACCEPT_POLYGAMY]
            : [MarriageType::ONLY_ONE_WIFE, MarriageType::ACCEPT_POLYGAMY];

        return [
            'marriage_statuses' => collect($availableStatuses)->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->labelForGender($isBrowsingMaleProfiles),
            ])->values(),
            'marriage_types' => collect($availableMarriageTypes)->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'body_shapes' => collect(BodyShape::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'skin_colors' => collect(SkinColor::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'education_levels' => collect(EducationLevel::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'financial_statuses' => collect(FinancialStatus::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'devotion_levels' => collect(DevotionType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'prayer_frequencies' => collect(PrayerType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->values(),
            'smoking_options' => [
                ['value' => 0, 'label' => trans('smart.non_smoker')],
                ['value' => 1, 'label' => trans('smart.smoker')],
            ],
        ];
    }
}
