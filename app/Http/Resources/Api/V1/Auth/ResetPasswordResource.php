<?php

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResetPasswordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'email' => $this['email'],
            'reset' => true,
        ];
    }
}
