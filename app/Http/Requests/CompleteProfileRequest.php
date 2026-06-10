<?php

namespace App\Http\Requests;

use App\Models\Enums\RegistrationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteProfileRequest extends FormRequest
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
        $isHusband = auth()->user()->registration_type === RegistrationType::AS_HUSBAND;

        return [
            // Familial status
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'marriage_type' => ['required', 'integer'],
            'marriage_status' => ['required', 'integer'],
            'child_count' => ['required', 'integer', 'min:0', 'max:50'],

            // Personal Information
            'residence' => ['required', 'integer'],
            'nationality' => ['required', 'integer'],
            'city' => ['required', 'integer', 'exists:cities,id'],
            'religion' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:30', 'max:300'],
            'height' => ['required', 'numeric', 'min:100', 'max:250'],
            'skin_color' => ['required', 'integer'],
            'body_shape' => ['required', 'integer'],

            // Lifestyle & Work
            'devotion' => ['required', 'integer'],
            'prayer' => ['required', 'integer'],
            'smoking' => ['required', 'integer'],
            'beard' => [Rule::requiredIf($isHusband), 'nullable', 'integer'],
            'education_level' => ['required', 'integer'],
            'financial_status' => ['required', 'integer'],
            'field_of_work' => ['required', 'integer'],
            'job' => ['required', 'string', 'max:255'],

            // Additional Information
            'monthly_income' => ['required', 'integer'],
            'health_status' => ['required', 'integer'],
            'about_partner' => ['required', 'string', 'max:5000'],
            'about_self' => ['required', 'string', 'max:5000'],
            'full_name' => ['required', 'string', 'max:255'],
        ];
    }
}
