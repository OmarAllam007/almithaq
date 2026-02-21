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
            'marriage_type' => ['nullable', 'integer'],
            'marriage_status' => ['nullable', 'integer'],
            'child_count' => ['nullable', 'integer', 'min:0', 'max:50'],

            // Location Information
            'residence' => ['nullable', 'integer', 'exists:countries,id'],
            'nationality' => ['nullable', 'integer', 'exists:countries,id'],
            'city' => ['nullable', 'max:255'],

            // Physical Attributes
            'weight' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'skin_color' => ['nullable', 'integer'],
            'body_shape' => ['nullable', 'integer'],

            // Religious & Social Information
            'devotion' => ['nullable', 'integer'],
            'prayer' => ['nullable', 'integer'],
            'smoking' => ['nullable', 'boolean'],
            'beard' => ['nullable', 'boolean'],

            // Professional Information
            'education_level' => ['nullable', 'integer'],
            'financial_status' => ['nullable', 'integer'],
            'field_of_work' => ['nullable', 'integer'],
            'job' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'health_status' => ['nullable', 'integer'],

            // About Section
            'about_partner' => ['nullable', 'string', 'max:5000'],
            'about_self' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
