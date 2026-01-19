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

    public function messages()
    {
        return [
            'username.required' => 'The username field is required.',
            'password.required' => 'The password field is required.',
            'username.exists' => 'The provided credentials does not match our records.',
            'password.required' => 'The provided credentials does not match our records.',
        ];
    }
}
