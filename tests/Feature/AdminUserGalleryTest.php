<?php

use App\Models\ProfileImage;
use App\Models\User;

it('shows a member photos on the admin edit page', function () {
    $admin = User::factory()->create(['is_admin' => true, 'profile_completed' => true]);
    $member = User::factory()->create();

    $image = ProfileImage::create([
        'user_id' => $member->id,
        'image_path' => 'profile_images/a.jpg',
        'thumbnail_path' => 'profile_images/a_thumb.jpg',
        'is_main' => true,
        'is_approved' => true,
        'order' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $member))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('images', 1)
            ->where('images.0.id', $image->id)
            ->where('images.0.is_main', true)
        );
});

it('lets an admin fetch a member original photo', function () {
    $admin = User::factory()->create(['is_admin' => true, 'profile_completed' => true]);
    $member = User::factory()->create();

    Storage::disk('local')->put('profile_images/a.jpg', 'binary');

    $image = ProfileImage::create([
        'user_id' => $member->id,
        'image_path' => 'profile_images/a.jpg',
        'thumbnail_path' => 'profile_images/a_thumb.jpg',
        'is_main' => true,
        'is_approved' => true,
        'order' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.approvals.original', $image))
        ->assertSuccessful();
});

it('denies a non admin the original photo route', function () {
    $member = User::factory()->create(['profile_completed' => true]);
    $other = User::factory()->create();

    $image = ProfileImage::create([
        'user_id' => $other->id,
        'image_path' => 'profile_images/a.jpg',
        'thumbnail_path' => 'profile_images/a_thumb.jpg',
        'is_main' => true,
        'is_approved' => true,
        'order' => 0,
    ]);

    $this->actingAs($member)
        ->get(route('admin.approvals.original', $image))
        ->assertForbidden();
});
