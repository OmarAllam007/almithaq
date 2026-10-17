<?php

use App\Mail\OnboardingReminderMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function admin(): User
{
    return User::factory()->create(['is_admin' => true, 'profile_completed' => true, 'email_verified_at' => now()]);
}

function unverified(array $attributes = []): User
{
    return User::factory()->create(array_merge(['email_verified_at' => null, 'profile_completed' => false], $attributes));
}

function incompleteProfile(array $attributes = []): User
{
    return User::factory()->create(array_merge(['email_verified_at' => now(), 'profile_completed' => false], $attributes));
}

it('lists only members who have not finished onboarding', function () {
    $stuckOnEmail = unverified();
    $stuckOnProfile = incompleteProfile();
    $finished = User::factory()->create(['email_verified_at' => now(), 'profile_completed' => true]);

    actingAs(admin());

    $page = get(route('admin.onboarding.index'))->assertSuccessful();

    $ids = collect($page->viewData('page')['props']['users']['data'])->pluck('id');

    expect($ids)->toContain($stuckOnEmail->id, $stuckOnProfile->id)
        ->not->toContain($finished->id);
});

it('labels the stage each member is stuck on', function () {
    unverified(['username' => 'needsemail']);
    incompleteProfile(['username' => 'needsprofile']);

    actingAs(admin());

    $rows = collect(get(route('admin.onboarding.index'))->viewData('page')['props']['users']['data'])
        ->pluck('stage', 'username');

    expect($rows['needsemail'])->toBe('email')
        ->and($rows['needsprofile'])->toBe('profile');
});

it('filters by stage, type, reminder state and search', function () {
    $email = unverified(['username' => 'alpha', 'registration_type' => 1]);
    $profile = incompleteProfile(['username' => 'beta', 'registration_type' => 2, 'onboarding_reminder_sent_at' => now()]);

    actingAs(admin());

    $ids = fn (array $query) => collect(get(route('admin.onboarding.index', $query))->viewData('page')['props']['users']['data'])->pluck('id');

    expect($ids(['stage' => 'email'])->all())->toBe([$email->id])
        ->and($ids(['stage' => 'profile'])->all())->toBe([$profile->id])
        ->and($ids(['registration_type' => 2])->all())->toBe([$profile->id])
        ->and($ids(['reminded' => '0'])->all())->toBe([$email->id])
        ->and($ids(['reminded' => '1'])->all())->toBe([$profile->id])
        ->and($ids(['search' => 'alph'])->all())->toBe([$email->id]);
});

it('reports how many members are stuck at each stage', function () {
    unverified();
    unverified();
    incompleteProfile();

    actingAs(admin());

    expect(get(route('admin.onboarding.index'))->viewData('page')['props']['counts'])
        ->toBe(['email' => 2, 'profile' => 1]);
});

it('sends the email verification reminder and records when it was sent', function () {
    Mail::fake();
    $user = unverified();

    actingAs(admin());

    post(route('admin.onboarding.remind', $user), ['lang' => 'en'])->assertRedirect();

    Mail::assertSent(OnboardingReminderMail::class, fn ($mail) => $mail->stage === 'email'
        && $mail->lang === 'en'
        && $mail->hasTo($user->email));

    expect($user->fresh()->onboarding_reminder_sent_at)->not->toBeNull();
});

it('sends the profile completion reminder to a verified member', function () {
    Mail::fake();
    $user = incompleteProfile();

    actingAs(admin());

    post(route('admin.onboarding.remind', $user), ['lang' => 'ar'])->assertRedirect();

    Mail::assertSent(OnboardingReminderMail::class, fn ($mail) => $mail->stage === 'profile' && $mail->lang === 'ar');
});

it('refuses to remind a member who already finished onboarding', function () {
    Mail::fake();
    $user = User::factory()->create(['email_verified_at' => now(), 'profile_completed' => true]);

    actingAs(admin());

    post(route('admin.onboarding.remind', $user), ['lang' => 'en'])->assertStatus(422);

    Mail::assertNothingSent();
});

it('rejects a missing or unsupported reminder language', function (array $payload) {
    Mail::fake();

    actingAs(admin());

    post(route('admin.onboarding.remind', unverified()), $payload)->assertSessionHasErrors('lang');

    Mail::assertNothingSent();
})->with([
    'missing' => [[]],
    'unsupported' => [['lang' => 'fr']],
]);

it('keeps the page behind the admin middleware', function () {
    actingAs(User::factory()->create(['is_admin' => false, 'profile_completed' => true]));

    get(route('admin.onboarding.index'))->assertForbidden();
});

it('renders both reminder emails in each language', function (string $lang) {
    expect((new OnboardingReminderMail(unverified(['name' => 'Omar']), $lang))->render())
        ->toContain('Omar')
        ->and((new OnboardingReminderMail(incompleteProfile(['name' => 'Omar']), $lang))->render())
        ->toContain('Omar');
})->with(['en', 'ar']);
