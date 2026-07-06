<?php

namespace App\Http\Requests\Api\V1\FirebaseToken;

use Illuminate\Foundation\Http\FormRequest;

class StoreTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'token' => 'required|string',
            'device_id' => 'required|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
