<?php

use App\Http\Middleware\CaptureUtmParameters;
use App\Models\User;
use App\Models\UserAcquisition;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

function signupPayload(array $overrides = []): array
{
    return array_merge([
        'registration_type' => 'husband',
        'name' => 'Omar Garana',
        'email' => 'omar@example.com',
        'username' => 'omargarana',
        'password' => 'password123',
        'age' => 30,
        'country_code' => '+20',
        'phone_number' => '1001234567',
    ], $overrides);
}

it('captures utm parameters from a campaign url into the session', function () {
    get('/?utm_source=facebook&utm_medium=paid_social&utm_campaign=khotobah_launch&utm_content=egypt')
        ->assertSuccessful()
        ->assertSessionHas(CaptureUtmParameters::SESSION_KEY, [
            'utm_source' => 'facebook',
            'utm_medium' => 'paid_social',
            'utm_campaign' => 'khotobah_launch',
            'utm_content' => 'egypt',
        ]);
});

it('keeps the first touch and ignores later campaigns', function () {
    get('/?utm_source=facebook&utm_campaign=launch');
    get('/?utm_source=google&utm_campaign=retarget');

    expect(session(CaptureUtmParameters::SESSION_KEY))
        ->toBe(['utm_source' => 'facebook', 'utm_campaign' => 'launch']);
});

it('persists attribution across navigation until registration', function () {
    Notification::fake();

    get('/?utm_source=facebook&utm_campaign=launch&utm_content=tunisia');
    get(route('signup'))->assertSuccessful();

    post(route('signup.store'), signupPayload())->assertRedirect(route('verification.notice'));

    expect(User::firstWhere('email', 'omar@example.com')->acquisition)
        ->utm_source->toBe('facebook')
        ->utm_campaign->toBe('launch')
        ->utm_content->toBe('tunisia');
});

it('stores nothing when the visitor arrives without utm parameters', function () {
    Notification::fake();

    get('/')->assertSessionMissing(CaptureUtmParameters::SESSION_KEY);
    post(route('signup.store'), signupPayload())->assertRedirect(route('verification.notice'));

    expect(UserAcquisition::count())->toBe(0);
    expect(User::firstWhere('email', 'omar@example.com'))->not->toBeNull();
});

it('ignores arbitrary query parameters', function () {
    get('/?utm_source=facebook&fbclid=abc123&email=someone@example.com&ref=spam');

    expect(session(CaptureUtmParameters::SESSION_KEY))->toBe(['utm_source' => 'facebook']);
});

it('sanitizes and truncates malformed or oversized values', function () {
    get('/?utm_source='.urlencode('<script>alert(1)</script>facebook').'&utm_campaign='.str_repeat('a', 300).'&utm_term=%20%20');

    $utm = session(CaptureUtmParameters::SESSION_KEY);

    expect($utm['utm_source'])->toBe('scriptalert1scriptfacebook')
        ->and(strlen($utm['utm_campaign']))->toBe(100)
        ->and($utm)->not->toHaveKey('utm_term');
});

it('ignores non scalar utm values', function () {
    get('/?utm_source[]=facebook&utm_source[]=google');

    expect(session(CaptureUtmParameters::SESSION_KEY))->toBeNull();
});

it('keeps one acquisition record per user', function () {
    $user = User::factory()->create();
    $user->acquisition()->create(['utm_source' => 'facebook']);

    expect(fn () => $user->acquisition()->create(['utm_source' => 'google']))
        ->toThrow(Illuminate\Database\QueryException::class);

    expect(UserAcquisition::where('user_id', $user->id)->count())->toBe(1);
});

it('does not re-attribute a second registration to the first campaign', function () {
    Notification::fake();

    get('/?utm_source=facebook&utm_campaign=launch');
    post(route('signup.store'), signupPayload());
    post(route('signup.store'), signupPayload(['email' => 'second@example.com', 'username' => 'second', 'phone_number' => '1009999999']));

    expect(UserAcquisition::count())->toBe(1);
});
