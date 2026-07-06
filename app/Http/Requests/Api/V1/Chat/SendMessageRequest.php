<?php

namespace App\Http\Requests\Api\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'message' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'image', 'max:10240'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message') && !$this->hasFile('attachment')) {
                $validator->errors()->add('message', 'Either message or attachment must be provided.');
            }
        });
    }
}
