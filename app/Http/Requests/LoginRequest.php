<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'exists:users,username'],
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => __('login.sign-in-error-username-required'),
            'password.required' => __('login.sign-in-error-password-required'),
            'username.exists' => __('login.sign-in-error-credentials'),
        ];
    }
}
