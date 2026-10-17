<?php

use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

it('records the geolocated login for a public ip', function () {
    Http::fake(['ip-api.com/*' => Http::response([
        'status' => 'success',
        'country' => 'Saudi Arabia',
        'countryCode' => 'SA',
        'city' => 'Dammam',
        'isp' => 'Internet BroadBand',
    ])]);

    $user = User::factory()->create();
    request()->server->set('REMOTE_ADDR', '37.242.69.0');

    event(new Illuminate\Auth\Events\Login('web', $user, false));

    expect($user->getLastLoginLog())
        ->country->toBe('Saudi Arabia')
        ->country_code->toBe('SA')
        ->city->toBe('Dammam')
        ->isp->toBe('Internet BroadBand');
});

it('skips private and loopback addresses', function (string $ip) {
    Http::fake();
    $user = User::factory()->create();
    request()->server->set('REMOTE_ADDR', $ip);

    event(new Illuminate\Auth\Events\Login('web', $user, false));

    expect(UserLoginLog::count())->toBe(0);
    Http::assertNothingSent();
})->with(['127.0.0.1', '::1', '192.168.1.5', '10.0.0.3']);

it('still logs the login when the geo lookup fails', function () {
    Http::fake(['ip-api.com/*' => Http::response(null, 500)]);
    $user = User::factory()->create();
    request()->server->set('REMOTE_ADDR', '37.242.69.0');

    event(new Illuminate\Auth\Events\Login('web', $user, false));

    expect($user->getLastLoginLog())->not->toBeNull()
        ->country->toBeNull();
});

it('exposes the last login location over the endpoint', function () {
    $viewer = User::factory()->create(['profile_completed' => true]);
    $target = User::factory()->create();
    $target->loginLogs()->create([
        'ip_address' => '37.242.69.0',
        'country' => 'Saudi Arabia',
        'city' => 'Dammam',
        'logged_at' => now(),
    ]);

    actingAs($viewer);

    getJson(route('users.login-location', $target))
        ->assertSuccessful()
        ->assertJson(['country' => 'Saudi Arabia', 'city' => 'Dammam']);
});

it('returns nulls when the user never logged in', function () {
    actingAs(User::factory()->create(['profile_completed' => true]));

    getJson(route('users.login-location', User::factory()->create()))
        ->assertSuccessful()
        ->assertJson(['country' => null, 'city' => null]);
});
