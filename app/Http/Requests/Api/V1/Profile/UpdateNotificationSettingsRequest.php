<?php

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
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
            'enable_notification' => ['sometimes', 'boolean'],
            'new_message'         => ['sometimes', 'boolean'],
            'marketing'           => ['sometimes', 'boolean'],
            'email_notification'  => ['sometimes', 'boolean'],
        ];
    }
}
