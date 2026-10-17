<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendOnboardingReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'lang' => ['required', 'in:en,ar'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lang.required' => 'Choose the language of the reminder.',
            'lang.in' => 'The reminder language must be English or Arabic.',
        ];
    }
}
