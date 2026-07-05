<?php

namespace App\Http\Requests\Api\V1\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReportUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_user_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['required', 'string'],
            'other_note' => ['nullable', 'string'],
        ];
    }
}
