<?php

namespace App\Http\Requests\Api\V1\FirebaseToken;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('user_id') && auth('api')->check()) {
            $this->merge(['user_id' => auth('api')->id()]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id'   => 'nullable|integer',
            'device_id' => 'required|string',
        ];
    }
}
