<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Account
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'registration_type' => ['nullable', 'integer', Rule::in([1, 2])],
            'is_active' => ['boolean'],
            'is_verified' => ['boolean'],
            'is_admin' => ['boolean'],

            // Personal
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'marriage_type' => ['nullable', 'integer'],
            'marriage_status' => ['nullable', 'integer'],
            'child_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'religion' => ['nullable', 'string', 'max:255'],
            'ethnicity' => ['nullable', 'string', 'max:255'],

            // Location
            'nationality' => ['nullable', 'integer', 'exists:countries,id'],
            'residence' => ['nullable', 'integer', 'exists:countries,id'],
            'city' => ['nullable', 'integer', 'exists:cities,id'],

            // Physical
            'weight' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'skin_color' => ['nullable', 'integer'],
            'body_shape' => ['nullable', 'integer'],

            // Religious / Social
            'devotion' => ['nullable', 'integer'],
            'prayer' => ['nullable', 'integer'],
            'smoking' => ['nullable', 'boolean'],
            'beard' => ['nullable', 'boolean'],

            // Professional
            'education_level' => ['nullable', 'integer'],
            'financial_status' => ['nullable', 'integer'],
            'field_of_work' => ['nullable', 'integer'],
            'job' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'integer'],
            'health_status' => ['nullable', 'integer'],

            // About
            'about_self' => ['nullable', 'string', 'max:5000'],
            'about_partner' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
