<?php

namespace App\Http\Requests\Api\V1\Location;

use Illuminate\Foundation\Http\FormRequest;

class SelectLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'province'    => 'required|string',
            'city'        => 'required|string',
            'guest_token' => 'nullable|string',
        ];
    }
}
