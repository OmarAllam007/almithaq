<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showSignupForm()
    {
        $countries = Country::orderBy('name')->select(['id', 'name', 'ar_name', 'flag'])->get();
        $devotions = config('list.devotions');
        $prayerCommitments = config('list.prayer_commitments');
        $yesNoList = config('list.yes_no_list');
        $education_levels = config('list.education_levels');
        $financial_statuses = config('list.financial_statuses');
        $fields_of_work = config('list.fields_of_work');

        return Inertia::render('Auth/Signup', [
            'countries' => $countries,
            'devotions' => $devotions,
            'prayer_commitments' => $prayerCommitments,
            'yes_no_list' => $yesNoList,
            'education_levels' => $education_levels,
            'financial_statuses' => $financial_statuses,
            'fields_of_work' => $fields_of_work,
        ]);
    }

    public function store(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        Auth::login($user);

        return redirect()->route('home');
    }
}
