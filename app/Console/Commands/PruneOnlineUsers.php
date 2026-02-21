<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class PruneOnlineUsers extends Command
{
    protected $signature = 'app:prune-online-users';

    protected $description = 'Prune online users from Redis';

    public function handle()
    {
        $tenMinutesAgo = now()->subMinutes(1)->timestamp;
        $removed = Redis::zremrangebyscore('online_users', '-inf', $tenMinutesAgo);
        $this->info("Pruned $removed inactive users.");
    }
}
