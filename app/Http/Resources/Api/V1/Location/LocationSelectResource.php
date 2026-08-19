<?php

namespace App\Http\Resources\Api\V1\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationSelectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language', 'en');
        $loc = \App\Helpers\translateHelper::translateLocation($this->province, $this->city, $locale);

        return [
            'user_id'          => $this->id,
            'province'         => $loc['province'],
            'city'             => $loc['city'],
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'role'             => $this->role,
            'guest_token'      => $this->guest_token,
            'guest_expires_at' => $this->guest_expires_at,
        ];
    }
}
