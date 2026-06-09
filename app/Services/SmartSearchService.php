<?php

namespace App\Services;

use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\ImageRequest;
use App\Models\User;

class SmartSearchService
{
    public function search(array $filters)
    {
        $currentUser = auth()->user();
        $oppositeType = $currentUser->registration_type === 1 ? 2 : 1;

        $query = User::query()
            ->where('id', '!=', $currentUser->id)
            ->where('registration_type', $oppositeType)
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

        // Marriage type filter (checkboxes)
        if (! empty($filters['marriage_type'])) {
            $query->whereIn('marriage_type', $filters['marriage_type']);
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

        $paginator = $query->paginate(20);
        $userIds = $paginator->pluck('id')->all();
        $currentUserId = $currentUser->id;
        $viewableIds = ImageRequest::approvedViewersOf($currentUserId, $userIds);

        return $paginator->through(function (User $user) use ($currentUserId, $viewableIds) {
            $canViewImages = isset($viewableIds[$user->id]);
            $image = $user->mainProfileImage->first();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'age' => $user->age,
                'nationality' => $user->nationality,
                'residence' => $user->residence,
                'marriage_status' => $user->marriage_status,
                'mainProfileImage' => $canViewImages
                    ? ($image?->original_url ?? $image?->thumbnail_url)
                    : $image?->thumbnail_url,
                'can_view_images' => $canViewImages,
                'is_online' => $user->is_online,
                'is_favorited' => $user->isFavoritedBy($currentUserId),
                'is_ignored' => $user->isIgnored($currentUserId),
                'is_verified' => (bool) $user->is_verified,
                'is_subscriber' => $user->hasActiveSubscription(),
                'registration_type' => $user->registration_type,
                'marriage_status_label' => MarriageStatus::tryFrom($user->marriage_status)?->label(),
                'marriage_type_label' => MarriageType::tryFrom($user->marriage_type)?->label(),
            ];
        });
    }

    public function getCachedFilters(): ?array
    {
        return auth()->user()?->smart_search_filters;
    }

    public function cacheFilters(array $filters): void
    {
        auth()->user()->update(['smart_search_filters' => $filters]);
    }

    public function clearCachedFilters(): void
    {
        auth()->user()->update(['smart_search_filters' => null]);
    }
}
