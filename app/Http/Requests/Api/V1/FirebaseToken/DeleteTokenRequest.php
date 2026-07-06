<?php

namespace App\Http\Requests\Api\V1\FirebaseToken;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'device_id' => 'required|string',
        ];
    }
}
