<?php

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

it('sends a reset code over email and masks the destination', function () {
    Notification::fake();
    $user = User::factory()->create(['username' => 'aisha', 'email' => 'aisha@example.com']);

    postJson(route('password.forgot.send'), ['username' => 'aisha', 'channel' => 'email'])
        ->assertSuccessful()
        ->assertJsonPath('channel', 'email')
        ->assertJsonPath('destination', 'a****@example.com');

    Notification::assertSentTo($user, PasswordResetCodeNotification::class);
    expect(PasswordResetCode::where('user_id', $user->id)->count())->toBe(1);
});

it('sends a reset code over whatsapp via the service', function () {
    mock(WhatsAppService::class)
        ->shouldReceive('sendResetCode')
        ->once();

    $user = User::factory()->create([
        'username' => 'omar',
        'country_code' => '+966',
        'phone_number' => '512345678',
    ]);

    postJson(route('password.forgot.send'), ['username' => 'omar', 'channel' => 'whatsapp'])
        ->assertSuccessful()
        ->assertJsonPath('channel', 'whatsapp');

    expect(PasswordResetCode::where('user_id', $user->id)->count())->toBe(1);
});

it('rejects an unknown username', function () {
    postJson(route('password.forgot.send'), ['username' => 'ghost', 'channel' => 'whatsapp'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('rejects email channel when the account has no email', function () {
    User::factory()->create(['username' => 'noemail', 'email' => null]);

    postJson(route('password.forgot.send'), ['username' => 'noemail', 'channel' => 'email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('channel');
});

it('resets the password with a valid code', function () {
    Notification::fake();
    $user = User::factory()->create(['username' => 'aisha', 'email' => 'aisha@example.com']);

    PasswordResetCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    postJson(route('password.forgot.reset'), [
        'username' => 'aisha',
        'code' => '123456',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertSuccessful();

    expect(Hash::check('newpassword1', $user->fresh()->password))->toBeTrue();
    expect(PasswordResetCode::where('user_id', $user->id)->count())->toBe(0);
});

it('rejects an incorrect code and tracks attempts', function () {
    $user = User::factory()->create(['username' => 'aisha']);

    $record = PasswordResetCode::create([
        'user_id' => $user->id,
        'channel' => 'whatsapp',
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    postJson(route('password.forgot.reset'), [
        'username' => 'aisha',
        'code' => '000000',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertStatus(422)->assertJsonValidationErrors('code');

    expect($record->fresh()->attempts)->toBe(1);
});

it('rejects an expired code', function () {
    $user = User::factory()->create(['username' => 'aisha']);

    PasswordResetCode::create([
        'user_id' => $user->id,
        'channel' => 'whatsapp',
        'code' => Hash::make('123456'),
        'expires_at' => now()->subMinute(),
    ]);

    postJson(route('password.forgot.reset'), [
        'username' => 'aisha',
        'code' => '123456',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertStatus(422)->assertJsonValidationErrors('code');
});
