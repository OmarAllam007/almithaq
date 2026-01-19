<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Account Information
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->user()->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],

            // Personal Information
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'marriage_type' => ['nullable', 'string', 'max:255'],
            'marriage_status' => ['nullable', 'string', 'max:255'],
            'child_count' => ['nullable', 'integer', 'min:0', 'max:50'],

            // Location Information
            'residence' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],

            // Physical Attributes
            'weight' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'skin_color' => ['nullable', 'string', 'max:255'],
            'body_shape' => ['nullable', 'string', 'max:255'],

            // Religious & Social Information
            'religion' => ['nullable', 'string', 'max:255'],
            'ethnicity' => ['nullable', 'string', 'max:255'],
            'devotion' => ['nullable', 'string', 'max:255'],
            'prayer' => ['nullable', 'string', 'max:255'],
            'smoking' => ['nullable', 'string', 'max:255'],
            'beard' => ['nullable', 'string', 'max:255'],

            // Professional Information
            'education_level' => ['nullable', 'string', 'max:255'],
            'financial_status' => ['nullable', 'string', 'max:255'],
            'field_of_work' => ['nullable', 'string', 'max:255'],
            'job' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'health_status' => ['nullable', 'string', 'max:255'],

            // About Section
            'about_partner' => ['nullable', 'string', 'max:5000'],
            'about_self' => ['nullable', 'string', 'max:5000'],
            'full_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
