<?php

namespace App\Http\Requests\Api\V1\FirebaseToken;

use Illuminate\Foundation\Http\FormRequest;

class StoreTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('token') && $this->filled('fcm_token')) {
            $this->merge(['token' => $this->input('fcm_token')]);
        }
        if (!$this->filled('user_id') && auth('api')->check()) {
            $this->merge(['user_id' => auth('api')->id()]);
        }
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
