<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Services\SmartSearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmartSearchController extends Controller
{
    public function index(Request $request, SmartSearchService $searchService)
    {
        $cachedFilters = $searchService->getCachedFilters();
        $users = null;

        // If there are cached filters, perform the search (for pagination)
        if ($cachedFilters && $request->has('page')) {
            $users = $searchService->search($cachedFilters);
        } elseif ($cachedFilters) {
            // Show filters by default even if we have cached filters
            $users = null;
        }

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        return Inertia::render('SmartSearch/Index', [
            'users' => $users,
            'cachedFilters' => $cachedFilters,
            'filterOptions' => $this->getFilterOptions(),
            'countries' => $countries,
        ]);
    }

    public function search(Request $request, SmartSearchService $searchService)
    {
        $filters = $request->only([
            'residence',
            'nationality',
            'marriage_status',
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

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

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

    private function getFilterOptions()
    {
        return [
            'marriage_statuses' => collect(MarriageStatus::cases())->map(fn ($case) => [
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
                ['value' => 0, 'label' => 'Non-Smoker'],
                ['value' => 1, 'label' => 'Smoker'],
            ],
        ];
    }
}
