<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                'phone_number',
                'nationality',
                'registration_type',
                'is_active',
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

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
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
                'is_verified' => (bool) $user->is_verified,
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
                'is_active' => $request->is_active,
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
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);

        return redirect()->back()->with('success', 'User unverified successfully.');
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
