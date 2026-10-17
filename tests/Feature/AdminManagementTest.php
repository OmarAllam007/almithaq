<?php

use App\Http\Middleware\TrackLastActive;
use App\Models\City;
use App\Models\Country;
use App\Models\Payment;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Redis::shouldReceive('zscore')->andReturn(null);
});

it('protects the control center and exposes the new dashboard route', function () {
    $member = User::factory()->create(['is_admin' => false]);
    $this->withoutMiddleware(TrackLastActive::class);

    $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
    $this->get('/admin/index/admin')->assertNotFound();

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
});

it('locks and unlocks a member without changing the old active flag', function () {
    $this->withoutMiddleware(TrackLastActive::class);
    $admin = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create(['is_active' => false, 'is_locked' => false]);

    $this->actingAs($admin)->post(route('admin.users.deactivate', $member))->assertRedirect();
    expect($member->fresh()->is_locked)->toBeTrue();
    expect((bool) $member->fresh()->is_active)->toBeFalse();

    $this->post(route('admin.users.activate', $member))->assertRedirect();
    expect($member->fresh()->is_locked)->toBeFalse();
});

it('refuses login for a locked account', function () {
    $member = User::factory()->create(['username' => 'locked-member', 'password' => Hash::make('password'), 'is_locked' => true]);
    $this->post(route('login.store'), ['username' => $member->username, 'password' => 'password'])
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

it('ends an existing session after an account is locked', function () {
    $member = User::factory()->create(['is_locked' => true]);
    $this->actingAs($member)->get(route('home'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('creates and edits location options through admin routes', function () {
    $this->withoutMiddleware(TrackLastActive::class);
    $admin = User::factory()->create(['is_admin' => true, 'nationality' => null, 'residence' => null, 'city' => null]);
    $this->actingAs($admin);

    $this->post(route('admin.countries.store'), ['name' => 'Exampleland', 'ar_name' => 'بلاد المثال', 'flag' => '🌍', 'is_arab' => false])->assertRedirect();
    $country = Country::where('name', 'Exampleland')->firstOrFail();
    $this->post(route('admin.cities.store'), ['country_id' => $country->id, 'name' => 'Example City', 'ar_name' => 'مدينة المثال'])->assertRedirect();
    $city = City::where('name', 'Example City')->firstOrFail();
    $this->put(route('admin.cities.update', $city), ['country_id' => $country->id, 'name' => 'New City'])->assertRedirect();
    expect($city->fresh()->name)->toBe('New City');
    $this->delete(route('admin.cities.destroy', $city))->assertRedirect();
    $this->delete(route('admin.countries.destroy', $country))->assertRedirect();
});

it('opens each admin workspace page', function () {
    $this->withoutMiddleware(TrackLastActive::class);
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    foreach (['admin.users.index', 'admin.verifications.index', 'admin.approvals.index', 'admin.payments.index', 'admin.countries.index', 'admin.cities.index'] as $name) {
        $this->get(route($name))->assertOk();
    }
});

it('requires a review note before rejecting a verification video', function () {
    $this->withoutMiddleware(TrackLastActive::class);
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $member = User::factory()->create(['verification_video_path' => 'verification_videos/example.mp4']);

    $this->post(route('admin.verifications.reject', $member), ['note' => ''])->assertSessionHasErrors('note');
    expect($member->fresh()->verification_video_path)->toBe('verification_videos/example.mp4');

    $this->post(route('admin.verifications.reject', $member), ['note' => 'Please record the video again in better lighting.'])->assertRedirect();
    expect($member->fresh()->verification_review_note)->toContain('better lighting');
    expect($member->fresh()->verification_video_path)->toBeNull();
});

it('reviews uploaded images and lists gateway payments', function () {
    Storage::fake('local');
    $this->withoutMiddleware(TrackLastActive::class);
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $member = User::factory()->create();
    $image = ProfileImage::create([
        'user_id' => $member->id,
        'image_path' => 'profile-images/example.jpg',
        'thumbnail_path' => 'profile-images/thumbnails/example.jpg',
        'is_main' => false,
        'is_approved' => false,
        'order' => 0,
    ]);
    Storage::disk('local')->put($image->thumbnail_path, 'thumbnail');

    $this->get(route('admin.approvals.thumbnail', $image))->assertOk();
    $this->patch(route('admin.approvals.approve', $image))->assertRedirect();
    expect($image->fresh()->is_approved)->toBeTrue();

    Payment::create(['user_id' => $member->id, 'amount' => 10, 'currency' => 'USD', 'gateway' => 'paymob', 'status' => 'pending']);
    $this->get(route('admin.payments.index'))->assertOk();
});
