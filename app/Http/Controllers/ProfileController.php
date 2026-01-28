<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\Country;
use App\Models\Enums\DeleteAccountReason;
use App\Models\ProfileVisit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $deleteAccountReasons = collect(DeleteAccountReason::cases())->map(fn ($reason) => [
            'value' => $reason->value,
            'label' => $reason->label(),
        ])->toArray();

        return Inertia::render('Profile/ProfileIndex', [
            'countries' => Country::orderBy('name')->select(['id', 'name', 'ar_name', 'flag'])->get(),
            'devotions' => config('list.devotions'),
            'prayer_commitments' => config('list.prayer_commitments'),
            'yes_no_list' => config('list.yes_no_list'),
            'education_levels' => config('list.education_levels'),
            'financial_statuses' => config('list.financial_statuses'),
            'fields_of_work' => config('list.fields_of_work'),
            'delete_account_reasons' => $deleteAccountReasons,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function show(User $user): JsonResponse
    {

        if (auth()->check() && auth()->id() !== $user->id) {
            ProfileVisit::create([
                'visitor_id' => auth()->id(),
                'visited_user_id' => $user->id,
            ]);
        }

        $userData = UserProfileResource::make($user);

        return response()->json(['user' => $userData]);
    }
}
