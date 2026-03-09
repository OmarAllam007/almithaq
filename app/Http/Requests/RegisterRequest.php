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
            'registration_type' => ['required', 'in:wife,husband'],
            'username' => ['required', 'string', 'max:20', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
        ];
    }
}
