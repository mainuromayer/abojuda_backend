<?php

namespace App\Http\Resources\Api\V1\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationSelectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->get('lang') ?? $request->header('lang') ?? (substr($request->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        $loc = \App\Helpers\translateHelper::translateLocation($this->province, $this->city, $locale);

        $address = null;
        if (method_exists($this->resource, 'getTranslation')) {
            $address = $this->resource->getTranslation('address', $locale, false);
        }
        if (empty($address)) {
            $address = $this->address;
        }
        if (empty($address) && !empty($loc['province'])) {
            $address = $locale === 'ar'
                ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
                : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));
        }

        $fullLocation = $locale === 'ar'
            ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
            : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));

        return [
            'user_id'          => $this->id,
            'province'         => $loc['province'],
            'city'             => $loc['city'],
            'address'          => $address,
            'full_location'    => $fullLocation,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'role'             => $this->role,
            'guest_token'      => $this->guest_token,
            'guest_expires_at' => $this->guest_expires_at,
        ];
    }
}
