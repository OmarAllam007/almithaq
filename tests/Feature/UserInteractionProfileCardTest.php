<?php

use App\Models\ProfileVisit;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

it('shows the correct profile details on interaction cards', function (string $page, string $relationship) {
    Redis::shouldReceive('zscore')->andReturn(null);
    Redis::shouldReceive('zadd')->andReturn(1);

    $viewer = User::factory()->create(['profile_completed' => true]);
    $member = User::factory()->create([
        'name' => 'Kenza',
        'registration_type' => 2,
        'marriage_status' => 1,
        'marriage_type' => 3,
        'profile_completed' => true,
    ]);

    match ($relationship) {
        'liked' => $member->favorites()->attach($viewer),
        'visited' => ProfileVisit::create(['visitor_id' => $member->id, 'visited_user_id' => $viewer->id]),
        'favorite' => $viewer->favorites()->attach($member),
        'ignored' => $viewer->ignores()->attach($member),
    };

    $this->actingAs($viewer)
        ->get($page)
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('users.data.0.id', $member->id)
            ->where('users.data.0.name', 'Kenza')
            ->where('users.data.0.registration_type', 2)
            ->where('users.data.0.marriage_status', 1)
            ->where('users.data.0.marriage_type_label', trans('enums.marriage_type_only_one_wife'))
        );
})->with([
    ['/who-liked-me', 'liked'],
    ['/who-visited-me', 'visited'],
    ['/my-interactions', 'favorite'],
    ['/my-interactions?tab=ignored', 'ignored'],
]);
