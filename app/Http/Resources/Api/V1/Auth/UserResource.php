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
        $locale = $request->get('lang') ?? $request->header('lang') ?? (substr($request->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        $loc = \App\Helpers\translateHelper::translateLocation($this->province, $this->city, $locale);

        $address = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('address', $locale, false) : null) ?: $this->address;
        if (empty($address) && !empty($loc['province'])) {
            $address = $locale === 'ar'
                ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
                : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));
        }

        $fullLocation = $locale === 'ar'
            ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
            : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));

        return [
            'id'                 => $this->id,
            'uuid'               => $this->uuid,
            'name'               => $this->name,
            'email'              => $this->email,
            'province'           => $loc['province'],
            'city'               => $loc['city'],
            'address'            => $address,
            'full_location'      => $fullLocation,
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
