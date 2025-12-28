<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            // Step 1: Account Type
            'registration_type' => ['required', 'in:wife,husband'],

            // Step 2: Basic Account Information
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'marriage_type' => ['required', 'string', 'max:255'],
            'marriage_status' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'child_count' => ['required', 'integer', 'min:0', 'max:50'],

            // Step 3: Personal Information
            'residence' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'religion' => ['required', 'string', 'max:255'],
            'ethnicity' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:30', 'max:300'],
            'height' => ['required', 'numeric', 'min:100', 'max:250'],
            'skin_color' => ['required', 'string', 'max:255'],
            'body_shape' => ['required', 'string', 'max:255'],

            // Step 4: Lifestyle & Work
            'devotion' => ['required', 'string', 'max:255'],
            'prayer' => ['required', 'string', 'max:255'],
            'smoking' => ['required', 'string', 'max:255'],
            'beard' => ['required_if:registration_type,husband', 'nullable', 'string', 'max:255'],
            'education_level' => ['required', 'string', 'max:255'],
            'financial_status' => ['required', 'string', 'max:255'],
            'field_of_work' => ['required', 'string', 'max:255'],
            'job' => ['required', 'string', 'max:255'],

            // Step 5: Additional Information
            'monthly_income' => ['required', 'numeric', 'min:0'],
            'health_status' => ['required', 'string', 'max:255'],
            'about_partner' => ['required', 'string', 'max:5000'],
            'about_self' => ['required', 'string', 'max:5000'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
        ];
    }
}
