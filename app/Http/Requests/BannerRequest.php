<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
            'title'          => 'required|string|max:255',
            'url'            => 'nullable|url', // Optional, but must be a valid URL if provided
            'thumbnail'      => 'required|image|mimes:jpeg,png,jpg,gif',
            'banner_image'   => 'required|image|mimes:jpeg,png,jpg,gif',
            'validity_start' => 'required|date',
            'validity_end'   => 'required|date|after_or_equal:validity_start',
            'status'         => 'required|in:active,inactive',
            'auto_publish'   => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'validity_end.after_or_equal' => 'The validity end date must be after or equal to the validity start date.',
        ];
    }
}
