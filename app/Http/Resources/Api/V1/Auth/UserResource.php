<?php

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'uuid'               => $this->uuid,
            'name'               => $this->name,
            'email'              => $this->email,
            'province'           => $this->province,
            'city'               => $this->city,
            'address'            => $this->address,
            'latitude'           => $this->latitude,
            'longitude'          => $this->longitude,
            'status'             => $this->status,
            'role'               => $this->role,
            'guest_token'        => $this->guest_token,
            'guest_expires_at'   => $this->guest_expires_at,
            'new_message'        => $this->new_message,
            'marketing'          => $this->marketing,
            'email_notification' => $this->email_notification,
        ];
    }
}
