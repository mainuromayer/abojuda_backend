<?php

namespace App\Http\Requests\Api\V1\Post;

use Illuminate\Foundation\Http\FormRequest;

class SaveDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_slug' => ['nullable', 'string', 'exists:categories,slug'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_category' => ['nullable', 'string', 'max:255'],
            'job_type' => ['nullable', 'array'],
            'job_type.*' => ['required', 'string'],
            'service_area' => ['nullable', 'array'],
            'service_area.*' => ['required', 'string'],
            'business_hours' => ['nullable', 'array'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'array'],
            'condition.*' => ['required', 'string'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_app_chat' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['required', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
        ];
    }
}
