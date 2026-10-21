<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Mail\OnboardingReminderMail;
use App\Models\City;
use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FieldOfWork;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\HealthStatusType;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\MonthlyIncomeType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->select([
                'id',
                'name',
                'username',
                'email',
                'email_verified_at',
                'phone_number',
                'nationality',
                'registration_type',
                'is_active',
                'is_locked',
                'is_verified',
                'last_seen_at',
                'created_at',
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->filled('is_locked')) {
            $query->where('is_locked', $request->is_locked);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'nationality' => $user->nationality,
                'registration_type' => $user->registration_type,
                'is_active' => (bool) $user->is_active,
                'is_locked' => (bool) $user->is_locked,
                'is_verified' => (bool) $user->is_verified,
                'is_email_verified' => ! is_null($user->email_verified_at),
                'is_online' => $user->isOnline(),
                'created_at' => $user->created_at?->format('Y-m-d H:i'),
            ];
        });

        $countries = Country::select(['id', 'name', 'ar_name'])->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'countries' => $countries,
            'filters' => [
                'nationality' => $request->nationality,
                'registration_type' => $request->registration_type,
                'is_locked' => $request->is_locked,
                'is_verified' => $request->is_verified,
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', $this->formData());
    }

    public function store(StoreAdminUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', array_merge($this->formData(), [
            'images' => $user->profileImages()->ordered()->get()->map(fn (ProfileImage $image) => [
                'id' => $image->id,
                'is_main' => $image->is_main,
                'is_approved' => $image->is_approved,
                'thumbnail_url' => route('admin.approvals.thumbnail', $image),
                'original_url' => route('admin.approvals.original', $image),
            ]),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'phone_number' => $user->phone_number,
                'registration_type' => $user->registration_type,
                'is_active' => (bool) $user->is_active,
                'is_locked' => (bool) $user->is_locked,
                'is_verified' => (bool) $user->is_verified,
                'is_admin' => (bool) $user->is_admin,
                'age' => $user->age,
                'marriage_type' => $user->marriage_type,
                'marriage_status' => $user->marriage_status,
                'child_count' => $user->child_count,
                'religion' => $user->religion,
                'ethnicity' => $user->ethnicity,
                'nationality' => $user->nationality,
                'residence' => $user->residence,
                'city' => $user->city,
                'weight' => $user->weight,
                'height' => $user->height,
                'skin_color' => $user->skin_color,
                'body_shape' => $user->body_shape,
                'devotion' => $user->devotion,
                'prayer' => $user->prayer,
                'smoking' => $user->smoking,
                'beard' => $user->beard,
                'education_level' => $user->education_level,
                'financial_status' => $user->financial_status,
                'field_of_work' => $user->field_of_work,
                'job' => $user->job,
                'monthly_income' => $user->monthly_income,
                'health_status' => $user->health_status,
                'about_self' => $user->about_self,
                'about_partner' => $user->about_partner,
            ],
        ]));
    }

    public function update(UpdateAdminUserRequest $request, User $user)
    {
        $data = $request->validated();

        abort_if($user->id === auth()->id() && array_key_exists('is_admin', $data) && ! $data['is_admin'], 422, 'You cannot remove your own admin access.');
        unset($data['is_active'], $data['is_verified']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot delete your own account here.');
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function deactivate(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot lock your own account.');
        $user->update(['is_locked' => true]);

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function activate(User $user)
    {
        $user->update(['is_locked' => false]);

        return redirect()->back()->with('success', 'User unlocked successfully.');
    }

    public function verify(User $user)
    {
        $user->update(['is_verified' => true]);

        return redirect()->back()->with('success', 'User verified successfully.');
    }

    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);

        return redirect()->back()->with('success', 'User unverified successfully.');
    }

    public function sendVerificationReminder(Request $request, User $user)
    {
        $request->validate(['lang' => ['required', 'in:en,ar']]);

        abort_if(empty($user->email), 422, 'User has no email address.');
        abort_if(! is_null($user->email_verified_at), 422, 'User email is already verified.');

        Mail::to($user->email)->send(new OnboardingReminderMail($user, $request->lang));

        $user->forceFill(['onboarding_reminder_sent_at' => now()])->save();

        return redirect()->back()->with('success', "Verification reminder sent to {$user->email}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $countries = Country::select(['id', 'name', 'ar_name'])->orderBy('name')->get();
        $cities = City::select(['id', 'country_id', 'name', 'ar_name'])->orderBy('name')->get();

        $enumOptions = [
            'marriage_types' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], MarriageType::cases()),
            'marriage_statuses' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->labelForGender(true)], MarriageStatus::cases()),
            'skin_colors' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], SkinColor::cases()),
            'body_shapes' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], BodyShape::cases()),
            'devotion_types' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], DevotionType::cases()),
            'prayer_types' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], PrayerType::cases()),
            'education_levels' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], EducationLevel::cases()),
            'financial_statuses' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], FinancialStatus::cases()),
            'fields_of_work' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], FieldOfWork::cases()),
            'monthly_incomes' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], MonthlyIncomeType::cases()),
            'health_statuses' => array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], HealthStatusType::cases()),
        ];

        return array_merge(['countries' => $countries, 'cities' => $cities], $enumOptions);
    }
}
