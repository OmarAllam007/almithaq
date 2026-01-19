<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SmartSearchService
{
    public function search(array $filters)
    {
        $query = User::query()
            ->where('id', '!=', auth()->id())
            ->with('mainProfileImage');

        // Residence filter (multi-select)
        if (! empty($filters['residence'])) {
            $query->whereIn('residence', $filters['residence']);
        }

        // Nationality filter (multi-select)
        if (! empty($filters['nationality'])) {
            $query->whereIn('nationality', $filters['nationality']);
        }

        // Marriage status filter (checkboxes)
        if (! empty($filters['marriage_status'])) {
            $query->whereIn('marriage_status', $filters['marriage_status']);
        }

        // Age range filter (slider)
        if (! empty($filters['age_min'])) {
            $query->where('age', '>=', $filters['age_min']);
        }
        if (! empty($filters['age_max'])) {
            $query->where('age', '<=', $filters['age_max']);
        }

        // Body shape filter (multi-select)
        if (! empty($filters['body_shape'])) {
            $query->whereIn('body_shape', $filters['body_shape']);
        }

        // Skin color filter (multi-select)
        if (! empty($filters['skin_color'])) {
            $query->whereIn('skin_color', $filters['skin_color']);
        }

        // Weight range filter (slider)
        if (! empty($filters['weight_min'])) {
            $query->where('weight', '>=', $filters['weight_min']);
        }
        if (! empty($filters['weight_max'])) {
            $query->where('weight', '<=', $filters['weight_max']);
        }

        // Height range filter (slider)
        if (! empty($filters['height_min'])) {
            $query->where('height', '>=', $filters['height_min']);
        }
        if (! empty($filters['height_max'])) {
            $query->where('height', '<=', $filters['height_max']);
        }

        // Education level filter (multi-select)
        if (! empty($filters['education_level'])) {
            $query->whereIn('education_level', $filters['education_level']);
        }

        // Financial status filter (multi-select)
        if (! empty($filters['financial_status'])) {
            $query->whereIn('financial_status', $filters['financial_status']);
        }

        // Devotion filter (multi-select)
        if (! empty($filters['devotion'])) {
            $query->whereIn('devotion', $filters['devotion']);
        }

        // Prayer filter (multi-select)
        if (! empty($filters['prayer'])) {
            $query->whereIn('prayer', $filters['prayer']);
        }

        // Smoking filter (boolean: 0 = non-smoker, 1 = smoker)
        if (isset($filters['smoking']) && is_array($filters['smoking']) && count($filters['smoking']) > 0) {
            $query->whereIn('smoking', $filters['smoking']);
        }

        return $query->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'age' => $user->age,
                    'nationality' => $user->nationality,
                    'residence' => $user->residence,
                    'marriage_status' => $user->marriage_status,
                    'mainProfileImage' => $user->mainProfileImage()->first()->image_path ?? null,
                    'is_online' => $user->is_online,
                    'is_favorited' => $user->isFavoritedBy(auth()->id()),
                ];
            });
    }

    public function getCachedFilters()
    {
        return Cache::get('smart_search_filters_'.auth()->id());
    }

    public function cacheFilters(array $filters)
    {
        Cache::put('smart_search_filters_'.auth()->id(), $filters, now()->addDays(30));
    }

    public function clearCachedFilters()
    {
        Cache::forget('smart_search_filters_'.auth()->id());
    }
}
