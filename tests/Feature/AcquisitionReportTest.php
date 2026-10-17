<?php

use App\Models\User;
use App\Services\AcquisitionReportService;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function acquired(array $utm, array $userAttributes = []): User
{
    $user = User::factory()->create($userAttributes);
    $user->acquisition()->create($utm);

    return $user;
}

function verificationUrl(User $user): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
}

it('counts the email verification milestone only once the email is actually verified', function () {
    $user = acquired(['utm_campaign' => 'launch'], ['email_verified_at' => null, 'profile_completed' => false]);

    actingAs($user);

    get(route('verification.notice'))->assertSuccessful();
    expect(app(AcquisitionReportService::class)->byDimension('utm_campaign')->first()['verified'])->toBe(0);

    get(verificationUrl($user))->assertRedirect(route('complete-profile'));

    expect($user->fresh()->email_verified_at)->not->toBeNull()
        ->and(app(AcquisitionReportService::class)->byDimension('utm_campaign')->first()['verified'])->toBe(1);
});

it('counts the profile completion milestone only after the profile is stored', function () {
    $user = acquired(['utm_campaign' => 'launch'], ['profile_completed' => false]);
    $report = app(AcquisitionReportService::class);

    actingAs($user);

    get(route('complete-profile'))->assertSuccessful();
    expect($report->byDimension('utm_campaign')->first()['completed'])->toBe(0);

    $user->update(['profile_completed' => true]);

    expect($report->byDimension('utm_campaign')->first()['completed'])->toBe(1);
});

it('aggregates the funnel by source, campaign and country content', function () {
    acquired(['utm_source' => 'facebook', 'utm_campaign' => 'launch', 'utm_content' => 'egypt'], ['email_verified_at' => now(), 'profile_completed' => true]);
    acquired(['utm_source' => 'facebook', 'utm_campaign' => 'launch', 'utm_content' => 'egypt'], ['email_verified_at' => now(), 'profile_completed' => false]);
    acquired(['utm_source' => 'facebook', 'utm_campaign' => 'launch', 'utm_content' => 'morocco'], ['email_verified_at' => null, 'profile_completed' => false]);
    acquired(['utm_source' => 'google', 'utm_campaign' => 'search', 'utm_content' => 'tunisia'], ['email_verified_at' => now(), 'profile_completed' => true]);

    $report = app(AcquisitionReportService::class);

    expect($report->byDimension('utm_source')->firstWhere('dimension', 'facebook'))
        ->registered->toBe(3)
        ->verified->toBe(2)
        ->completed->toBe(1)
        ->verified_rate->toBe(66.67)
        ->completed_rate->toBe(33.33);

    expect($report->byDimension('utm_campaign')->firstWhere('dimension', 'search'))
        ->registered->toBe(1)
        ->completed_rate->toBe(100.0);

    expect($report->byDimension('utm_content')->pluck('registered', 'dimension')->sortKeys()->all())
        ->toBe(['egypt' => 2, 'morocco' => 1, 'tunisia' => 1]);
});

it('counts acquired users per day and overall', function () {
    acquired(['utm_campaign' => 'launch'], ['created_at' => '2026-10-01 09:00:00', 'email_verified_at' => now(), 'profile_completed' => true]);
    acquired(['utm_campaign' => 'launch'], ['created_at' => '2026-10-01 20:00:00', 'email_verified_at' => null, 'profile_completed' => false]);
    acquired(['utm_campaign' => 'other'], ['created_at' => '2026-10-02 10:00:00', 'email_verified_at' => now(), 'profile_completed' => false]);

    $report = app(AcquisitionReportService::class);

    expect($report->perDay('launch')->all())->toBe([
        ['date' => '2026-10-01', 'registered' => 2],
    ]);

    expect($report->perDay()->all())->toBe([
        ['date' => '2026-10-01', 'registered' => 2],
        ['date' => '2026-10-02', 'registered' => 1],
    ]);

    expect($report->funnel())
        ->toBe(['registered' => 3, 'verified' => 2, 'completed' => 1, 'verified_rate' => 66.67, 'completed_rate' => 33.33]);
});

it('narrows reports to a date range', function () {
    acquired(['utm_campaign' => 'launch'], ['created_at' => '2026-09-01 10:00:00']);
    acquired(['utm_campaign' => 'launch'], ['created_at' => '2026-10-05 10:00:00']);

    expect(app(AcquisitionReportService::class)->byDimension('utm_campaign', '2026-10-01')->first()['registered'])->toBe(1);
});

it('rejects an unknown utm dimension', function () {
    app(AcquisitionReportService::class)->byDimension('password');
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);
