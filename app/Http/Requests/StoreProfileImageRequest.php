<?php

namespace App\Http\Requests;

use App\Models\ProfileImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProfileImageRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Please select at least one image to upload.',
            'images.max' => 'You can upload a maximum of 5 images at once.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or JPG format.',
            'images.*.max' => 'Each image must not exceed 5MB in size.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $currentImageCount = ProfileImage::where('user_id', $this->user()->id)->count();
            $newImageCount = is_array($this->images) ? count($this->images) : 0;
            $totalImageCount = $currentImageCount + $newImageCount;

            if ($totalImageCount > 5) {
                $validator->errors()->add(
                    'images',
                    "You can only have a maximum of 5 images. You currently have {$currentImageCount} image(s)."
                );
            }
        });
    }
}
