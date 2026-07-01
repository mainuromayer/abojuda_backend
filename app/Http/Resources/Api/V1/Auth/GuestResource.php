<?php

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            // 'guest_token'      => $this->guest_token,
            'guest_expires_at' => $this->guest_expires_at,
            'role'             => $this->role,
        ];
    }
}
