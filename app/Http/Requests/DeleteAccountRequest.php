<?php

namespace App\Http\Requests;

use App\Models\Enums\DeleteAccountReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(DeleteAccountReason::class)],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please select a reason for deleting your account.',
            'reason.enum' => 'Please select a valid reason.',
            'message.max' => 'The message must not exceed 1000 characters.',
        ];
    }
}
