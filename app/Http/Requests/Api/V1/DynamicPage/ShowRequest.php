<?php

namespace App\Http\Requests\Api\V1\DynamicPage;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'exists:dynamic_pages,slug'],
        ];
    }
}
