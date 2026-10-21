<?php

use App\Models\ImageRequest;
use App\Models\User;

it('skips image requests whose requester deleted their account', function () {
    $owner = User::factory()->create(['profile_completed' => true]);
    $requester = User::factory()->create(['profile_completed' => true]);
    $survivor = User::factory()->create(['profile_completed' => true]);

    ImageRequest::create([
        'requester_id' => $requester->id,
        'requested_user_id' => $owner->id,
        'status' => 'pending',
    ]);

    ImageRequest::create([
        'requester_id' => $survivor->id,
        'requested_user_id' => $owner->id,
        'status' => 'pending',
    ]);

    $requester->delete();

    $this->actingAs($owner)
        ->get(route('image-requests.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('requests', 1)
            ->where('requests.0.requester.id', $survivor->id)
        );
});

it('verifies new accounts without admin review', function () {
    $user = User::factory()->create();

    expect($user->fresh()->is_verified)->toBeTrue();
});
