<?php

use App\Models\Enums\NotificationType;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;

beforeEach(function () {
    $this->owner = User::factory()->create(['profile_completed' => true]);
});

it('hides notifications whose actor deleted their account', function () {
    $gone = User::factory()->create();
    $present = User::factory()->create();

    UserNotification::create([
        'user_id' => $this->owner->id,
        'actor_id' => $gone->id,
        'type' => NotificationType::PROFILE_VISIT,
    ]);

    UserNotification::create([
        'user_id' => $this->owner->id,
        'actor_id' => $present->id,
        'type' => NotificationType::LIKE,
    ]);

    $gone->delete();

    $response = $this->actingAs($this->owner)
        ->getJson(route('notifications.index'))
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.actor.id'))->toBe($present->id);
});

it('keeps notifications that never had an actor', function () {
    UserNotification::create([
        'user_id' => $this->owner->id,
        'actor_id' => null,
        'type' => NotificationType::SUBSCRIPTION_RENEWED,
    ]);

    $this->actingAs($this->owner)
        ->getJson(route('notifications.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('excludes deleted actors from the unread badge count', function () {
    $gone = User::factory()->create();

    UserNotification::create([
        'user_id' => $this->owner->id,
        'actor_id' => $gone->id,
        'type' => NotificationType::IMAGE_REQUEST,
    ]);

    $gone->delete();

    expect(app(NotificationService::class)->getUnreadCount($this->owner->id))->toBe(0);
});
