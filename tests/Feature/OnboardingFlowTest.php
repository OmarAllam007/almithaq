<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

function member(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'profile_completed' => true,
        'is_verified' => true,
    ], $attributes));
}

it('sends a new signup straight to the profile step instead of the inbox', function () {
    Notification::fake();

    post(route('signup.store'), [
        'registration_type' => 'husband',
        'name' => 'Omar Garana',
        'email' => 'omar@example.com',
        'username' => 'omargarana',
        'password' => 'password123',
        'age' => 30,
        'country_code' => '+20',
        'phone_number' => '1001234567',
    ])->assertRedirect(route('complete-profile'));

    expect(User::firstWhere('email', 'omar@example.com')->email_verified_at)->toBeNull();
});

it('still sends the verification email on signup', function () {
    Notification::fake();

    post(route('signup.store'), [
        'registration_type' => 'wife',
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'username' => 'saraahmed',
        'password' => 'password123',
        'age' => 27,
        'country_code' => '+20',
        'phone_number' => '1007654321',
    ]);

    Notification::assertSentTo(User::firstWhere('email', 'sara@example.com'), Illuminate\Auth\Notifications\VerifyEmail::class);
});

it('lets an unverified member complete their profile', function () {
    actingAs(member(['email_verified_at' => null, 'profile_completed' => false]));

    get(route('complete-profile'))->assertSuccessful();
});

it('lets an unverified member browse the platform', function (string $routeName) {
    actingAs(member(['email_verified_at' => null]));

    get(route($routeName))->assertSuccessful();
})->with(['home', 'new-members', 'smart-search.index', 'profile']);

it('blocks an unverified member from starting a conversation', function () {
    actingAs(member(['email_verified_at' => null]));

    postJson(route('conversations.store'), ['recipient_id' => member()->id])
        ->assertForbidden()
        ->assertJsonPath('reason', 'email_unverified');
});

it('blocks an unverified member from sending a message', function () {
    $sender = member(['email_verified_at' => null]);
    $recipient = member();

    actingAs($recipient);
    $conversationId = postJson(route('conversations.store'), ['recipient_id' => $sender->id])->json('conversation_id');

    actingAs($sender);
    postJson(route('messages.store', $conversationId), ['message' => 'Salam'])
        ->assertForbidden()
        ->assertJsonPath('reason', 'email_unverified');
});

it('lets a verified member send a message', function () {
    $sender = member();
    $recipient = member();

    actingAs($sender);

    $conversationId = postJson(route('conversations.store'), ['recipient_id' => $recipient->id])
        ->assertSuccessful()
        ->json('conversation_id');

    postJson(route('messages.store', $conversationId), ['message' => 'Salam'])->assertSuccessful();
});

it('accepts a profile without the two free-text answers', function () {
    $user = member([
        'email_verified_at' => null,
        'profile_completed' => false,
        'about_self' => null,
        'about_partner' => null,
    ]);

    actingAs($user);

    post(route('complete-profile.store'), profilePayload())->assertRedirect(route('home'));

    expect($user->fresh())
        ->profile_completed->toBeTrue()
        ->about_self->toBeNull()
        ->about_partner->toBeNull();
});

it('still accepts a profile that includes them', function () {
    $user = member(['email_verified_at' => null, 'profile_completed' => false]);

    actingAs($user);

    post(route('complete-profile.store'), profilePayload([
        'about_self' => 'A short introduction.',
        'about_partner' => 'What I am looking for.',
    ]))->assertRedirect(route('home'));

    expect($user->fresh()->about_self)->toBe('A short introduction.');
});

function testCityId(): int
{
    return App\Models\City::query()->value('id')
        ?? App\Models\City::query()->create([
            'country_id' => App\Models\Country::query()->create(['name' => 'Egypt', 'ar_name' => 'مصر', 'flag' => '🇪🇬'])->id,
            'name' => 'Cairo',
        ])->id;
}

function profilePayload(array $overrides = []): array
{
    return array_merge([
        'marriage_type' => 1,
        'marriage_status' => 1,
        'child_count' => 0,
        'residence' => 1,
        'nationality' => 1,
        'city' => testCityId(),
        'religion' => '1',
        'weight' => 80,
        'height' => 180,
        'skin_color' => 1,
        'body_shape' => 1,
        'devotion' => 1,
        'prayer' => 1,
        'smoking' => 0,
        'beard' => 1,
        'education_level' => 1,
        'financial_status' => 1,
        'field_of_work' => 1,
        'job' => 'Engineer',
        'monthly_income' => 1,
        'health_status' => 1,
        'full_name' => 'Omar Garana',
    ], $overrides);
}
