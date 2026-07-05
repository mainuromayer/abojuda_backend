<?php

namespace App\Http\Requests\Api\V1\Post;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'category_slug' => 'nullable|string|exists:categories,slug',
            'province'    => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:255',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'sort_by'     => 'nullable|string|in:featured,newest,oldest,price_low_high,price_high_low',
            'search'      => 'nullable|string|max:255',
        ];
    }
}
