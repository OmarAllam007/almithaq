<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->country_code && $this->phone_number) {
            $this->merge([
                'full_phone' => $this->country_code.$this->phone_number,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'registration_type' => ['required', 'in:wife,husband'],
            'username' => ['required', 'string', 'alpha', 'max:20', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'country_code' => ['required', 'string', 'max:5'],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->where('country_code', $this->country_code),
            ],
            'full_phone' => [(new Phone)->international()->type('mobile')],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        unset($data['full_phone']);

        return $data;
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('full_phone')) {
                $message = $validator->errors()->first('full_phone');
                $validator->errors()->forget('full_phone');
                $validator->errors()->add('phone_number', $message);
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_phone.phone' => __('signup.validation-phone-invalid'),
            'phone_number.unique' => __('signup.validation-phone-taken'),
            'phone_number.required' => __('signup.validation-phone-required'),
            'country_code.required' => __('signup.validation-country-code-required'),
        ];
    }
}
