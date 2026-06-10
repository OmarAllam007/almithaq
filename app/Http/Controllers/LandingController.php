<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return Inertia::render('Landing/Index', [
            'recentMembers' => $this->recentMembers(),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Recently online members shown publicly. Privacy-safe: no photos or
     * personal contact details are exposed to guests.
     *
     * @return array<int, array{id:int, username:string, age:int|null, is_verified:bool, is_online:bool}>
     */
    private function recentMembers(): array
    {
        $onlineIds = $this->onlineUserIds();

        $members = User::query()
            ->where('profile_completed', true)
            ->whereNotNull('username')
            ->when($onlineIds->isNotEmpty(), function ($query) use ($onlineIds): void {
                $query->whereIn('id', $onlineIds->all());
            }, function ($query): void {
                $query->orderByRaw('last_seen_at IS NULL')
                    ->latest('last_seen_at')
                    ->latest('id');
            })
            ->limit(8)
            ->get(['id', 'username', 'age', 'is_verified']);

        if ($onlineIds->isNotEmpty()) {
            $members = $members->sortBy(fn (User $user): int => $onlineIds->search($user->id))->values();
        }

        return $members->map(fn (User $user): array => [
            'id' => $user->id,
            'username' => $user->username,
            'age' => $user->age,
            'is_verified' => (bool) $user->is_verified,
            'is_online' => $onlineIds->contains($user->id),
        ])->all();
    }

    /**
     * @return Collection<int, int>
     */
    private function onlineUserIds(): Collection
    {
        try {
            $minTimestamp = now()->subMinutes(10)->timestamp;

            return collect(Redis::zrevrangebyscore('online_users', '+inf', $minTimestamp))
                ->map(fn ($id): int => (int) $id);
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * @return array{members:int, matches:int, countries:int, verified:int}
     */
    private function stats(): array
    {
        return [
            'members' => max(1200, User::query()->where('profile_completed', true)->count()),
            'matches' => 4800,
            'countries' => 32,
            'verified' => 95,
        ];
    }
}
