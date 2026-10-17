<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAcquisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AcquisitionReportService
{
    /**
     * Funnel counts grouped by one UTM dimension, e.g. 'utm_source',
     * 'utm_campaign' or 'utm_content' (country, by our naming convention).
     *
     * @return Collection<int, array{dimension: ?string, registered: int, verified: int, completed: int, verified_rate: float, completed_rate: float}>
     */
    public function byDimension(string $dimension, ?string $since = null, ?string $until = null): Collection
    {
        abort_unless(in_array($dimension, UserAcquisition::PARAMETERS, true), 500, 'Unknown UTM dimension.');

        return $this->baseQuery($since, $until)
            ->join('user_acquisitions', 'user_acquisitions.user_id', '=', 'users.id')
            ->groupBy("user_acquisitions.{$dimension}")
            ->selectRaw("user_acquisitions.{$dimension} as dimension")
            ->selectRaw('count(*) as registered')
            ->selectRaw('count(users.email_verified_at) as verified')
            ->selectRaw('sum(users.profile_completed) as completed')
            ->orderByDesc('registered')
            ->get()
            ->map(fn ($row) => $this->withRates([
                'dimension' => $row->dimension,
                'registered' => (int) $row->registered,
                'verified' => (int) $row->verified,
                'completed' => (int) $row->completed,
            ]));
    }

    /**
     * Acquired users per day, optionally narrowed to one campaign.
     *
     * @return Collection<int, array{date: string, registered: int}>
     */
    public function perDay(?string $campaign = null, ?string $since = null, ?string $until = null): Collection
    {
        return $this->baseQuery($since, $until)
            ->when($campaign !== null, fn (Builder $query) => $query->whereHas(
                'acquisition',
                fn (Builder $acquisition) => $acquisition->where('utm_campaign', $campaign)
            ))
            ->groupByRaw('date(users.created_at)')
            ->selectRaw('date(users.created_at) as date, count(*) as registered')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => (string) $row->date, 'registered' => (int) $row->registered]);
    }

    /**
     * Overall funnel across every registration, attributed or not.
     *
     * @return array{registered: int, verified: int, completed: int, verified_rate: float, completed_rate: float}
     */
    public function funnel(?string $since = null, ?string $until = null): array
    {
        $row = $this->baseQuery($since, $until)
            ->selectRaw('count(*) as registered')
            ->selectRaw('count(users.email_verified_at) as verified')
            ->selectRaw('sum(users.profile_completed) as completed')
            ->first();

        return $this->withRates([
            'registered' => (int) $row->registered,
            'verified' => (int) $row->verified,
            'completed' => (int) $row->completed,
        ]);
    }

    /**
     * @return Builder<User>
     */
    protected function baseQuery(?string $since, ?string $until): Builder
    {
        return User::query()
            ->when($since !== null, fn (Builder $query) => $query->where('users.created_at', '>=', $since))
            ->when($until !== null, fn (Builder $query) => $query->where('users.created_at', '<=', $until));
    }

    /**
     * @param  array{registered: int, verified: int, completed: int}  $counts
     * @return array{registered: int, verified: int, completed: int, verified_rate: float, completed_rate: float}
     */
    protected function withRates(array $counts): array
    {
        $registered = $counts['registered'];

        return $counts + [
            'verified_rate' => $registered > 0 ? round($counts['verified'] / $registered * 100, 2) : 0.0,
            'completed_rate' => $registered > 0 ? round($counts['completed'] / $registered * 100, 2) : 0.0,
        ];
    }
}
