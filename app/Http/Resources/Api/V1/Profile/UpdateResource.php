<?php

namespace App\Http\Resources\Api\V1\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'uuid'     => $this->uuid,
            'name'     => $this->name,
            'email'    => $this->email,
            'province' => $this->province,
            'city'     => $this->city,
            'address'  => $this->address,
            'role'     => $this->role,
        ];
    }
}
