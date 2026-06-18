<?php

namespace App\Console\Commands;

use App\Models\Enums\RegistrationType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ShuffleFakeOnlineUsers extends Command
{
    protected $signature = 'app:shuffle-fake-online-users';

    protected $description = 'Shuffle fake online users (no phone_number) in the online_users Redis set';

    public function handle(): void
    {
        $minPerGender = 50;

        // Remove all fake users (no phone_number) from the online set
        $allFakeIds = User::query()
            ->whereNull('phone_number')
            ->pluck('id')
            ->toArray();

        if (! empty($allFakeIds)) {
            Redis::zrem('online_users', ...$allFakeIds);
        }

        // Pick a new random selection: min 50 female + 50 male
        $femaleIds = User::query()
            ->whereNull('phone_number')
            ->where('registration_type', RegistrationType::AS_WIFE)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($minPerGender)
            ->pluck('id')
            ->toArray();

        $maleIds = User::query()
            ->whereNull('phone_number')
            ->where('registration_type', RegistrationType::AS_HUSBAND)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($minPerGender)
            ->pluck('id')
            ->toArray();

        // Score = 1 hour from now so PruneOnlineUsers won't touch them until next shuffle
        $score = now()->addHour()->timestamp;

        foreach (array_merge($femaleIds, $maleIds) as $userId) {
            Redis::zadd('online_users', $score, $userId);
        }

        $this->info(sprintf(
            'Shuffled fake online users: %d female, %d male.',
            count($femaleIds),
            count($maleIds),
        ));
    }
}
