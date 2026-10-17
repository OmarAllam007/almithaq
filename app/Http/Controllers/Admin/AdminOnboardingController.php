<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendOnboardingReminderRequest;
use App\Mail\OnboardingReminderMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AdminOnboardingController extends Controller
{
    public function index(Request $request): Response
    {
        $stuck = User::query()->where(fn (Builder $query) => $query
            ->whereNull('email_verified_at')
            ->orWhere('profile_completed', false)
        );

        $users = (clone $stuck)
            ->with('acquisition:id,user_id,utm_campaign,utm_source')
            ->when($request->filled('stage'), fn (Builder $query) => $request->stage === 'email'
                ? $query->whereNull('email_verified_at')
                : $query->whereNotNull('email_verified_at')->where('profile_completed', false))
            ->when($request->filled('registration_type'), fn (Builder $query) => $query->where('registration_type', $request->registration_type))
            ->when($request->filled('reminded'), fn (Builder $query) => $request->reminded === '1'
                ? $query->whereNotNull('onboarding_reminder_sent_at')
                : $query->whereNull('onboarding_reminder_sent_at'))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(fn (Builder $match) => $match
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('username', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
            ))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'registration_type' => $user->registration_type,
                'stage' => $user->email_verified_at === null ? 'email' : 'profile',
                'campaign' => $user->acquisition?->utm_campaign ?? $user->acquisition?->utm_source,
                'created_at' => $user->created_at?->toDateString(),
                'reminded_at' => $user->onboarding_reminder_sent_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Onboarding/Index', [
            'users' => $users,
            'filters' => $request->only(['stage', 'registration_type', 'reminded', 'search']),
            'counts' => [
                'email' => (clone $stuck)->whereNull('email_verified_at')->count(),
                'profile' => (clone $stuck)->whereNotNull('email_verified_at')->where('profile_completed', false)->count(),
            ],
        ]);
    }

    public function sendReminder(SendOnboardingReminderRequest $request, User $user): RedirectResponse
    {
        abort_if(blank($user->email), 422, 'This member has no email address.');
        abort_if($user->email_verified_at !== null && $user->profile_completed, 422, 'This member has already finished onboarding.');

        Mail::to($user->email)->send(new OnboardingReminderMail($user, $request->lang));

        $user->forceFill(['onboarding_reminder_sent_at' => now()])->save();

        return back()->with('success', "Reminder sent to {$user->email}.");
    }
}
