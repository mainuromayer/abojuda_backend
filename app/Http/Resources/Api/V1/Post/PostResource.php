<?php

namespace App\Http\Resources\Api\V1\Post;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photoUrls = [];
        if (is_array($this->photos)) {
            foreach ($this->photos as $path) {
                $url = $this->getImageUrl($path);
                if ($url) {
                    $photoUrls[] = $url;
                }
            }
        }

        $categoryName = $this->whenLoaded('category', fn() => $this->category?->name, $this->category?->name ?? null);
        $categoryLower = strtolower($categoryName ?? '');
        $categorySlug = $this->category?->slug ?? null;

        if ($categorySlug === 'buy-sell') {
            return [
                'user_id'       => $this->user_id,
                'user_name'     => $this->user?->name,
                'user_since'    => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'   => $this->category_id,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'photos'        => $photoUrls,
                'title'         => $this->title,
                'description'   => $this->description,
                'price'         => $this->price,
                'condition'     => $this->condition,
                'province'      => $this->province,
                'city'          => $this->city,
                'address'       => $this->address,
                'phone'         => $this->phone,
                'whatsapp'      => $this->whatsapp,
                'email'         => $this->email,
                'is_app_chat'   => (bool)$this->is_app_chat,
                'status'        => $this->status,
                'created_at'    => $this->created_at,
                'time_ago'      => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'      => $this->latitude,
                'longitude'     => $this->longitude,
                'distance'      => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        } elseif ($categorySlug === 'jobs') {
            return [
                'user_id'       => $this->user_id,
                'user_name'     => $this->user?->name,
                'user_since'    => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'   => $this->category_id,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'title'         => $this->title,
                'company_name'  => $this->company_name,
                'description'   => $this->description,
                'job_type'      => $this->job_type,
                'province'      => $this->province,
                'city'          => $this->city,
                'address'       => $this->address,
                'phone'         => $this->phone,
                'whatsapp'      => $this->whatsapp,
                'email'         => $this->email,
                'is_app_chat'   => (bool)$this->is_app_chat,
                'status'        => $this->status,
                'created_at'    => $this->created_at,
                'time_ago'      => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'      => $this->latitude,
                'longitude'     => $this->longitude,
                'distance'      => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        } elseif ($categorySlug === 'business-directory') {
            return [
                'user_id'           => $this->user_id,
                'user_name'         => $this->user?->name,
                'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'       => $this->category_id,
                'category_name'     => $categoryName,
                'category_slug'     => $categorySlug,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'business_name'     => $this->business_name,
                'business_category' => $this->business_category,
                'description'       => $this->description,
                'website'           => $this->website,
                'photos'            => $photoUrls,
                'province'          => $this->province,
                'city'              => $this->city,
                'address'           => $this->address,
                'phone'             => $this->phone,
                'whatsapp'          => $this->whatsapp,
                'email'             => $this->email,
                'is_app_chat'       => (bool)$this->is_app_chat,
                'logo'              => $this->getImageUrl($this->logo),
                'status'            => $this->status,
                'created_at'        => $this->created_at,
                'time_ago'          => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'          => $this->latitude,
                'longitude'         => $this->longitude,
                'distance'          => isset($this->distance) ? round($this->distance, 2) : null,
                'business_hours'    => $this->relationLoaded('businessHours') && $this->businessHours ? [
                    'sunday'    => $this->businessHours->sunday,
                    'monday'    => $this->businessHours->monday,
                    'tuesday'   => $this->businessHours->tuesday,
                    'wednesday' => $this->businessHours->wednesday,
                    'thursday'  => $this->businessHours->thursday,
                    'friday'    => $this->businessHours->friday,
                    'saturday'  => $this->businessHours->saturday,
                ] : null,
            ];
        } elseif ($categorySlug === 'services') {
            return [
                'user_id'       => $this->user_id,
                'user_name'     => $this->user?->name,
                'user_since'    => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'   => $this->category_id,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'photos'        => $photoUrls,
                'title'         => $this->title,
                'description'   => $this->description,
                'service_area'  => $this->service_area,
                'province'      => $this->province,
                'city'          => $this->city,
                'address'       => $this->address,
                'phone'         => $this->phone,
                'whatsapp'      => $this->whatsapp,
                'email'         => $this->email,
                'is_app_chat'   => (bool)$this->is_app_chat,
                'status'        => $this->status,
                'created_at'    => $this->created_at,
                'time_ago'      => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'      => $this->latitude,
                'longitude'     => $this->longitude,
                'distance'      => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        }

        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'user_name'     => $this->user?->name,
            'user_since'    => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
            'category_id'   => $this->category_id,
            'category_name' => $categoryName,
            'category_slug' => $categorySlug,
            'thumbnail'     => $this->getImageUrl($this->thumbnail),
            'title'         => $this->title,
            'business_name' => $this->business_name,
            'company_name'  => $this->company_name,
            'business_category' => $this->business_category,
            'description'   => $this->description,
            'price'         => $this->price,
            'condition'     => $this->condition,
            'job_type'      => $this->job_type,
            'service_area'  => $this->service_area,
            'website'       => $this->website,
            'logo'          => $this->getImageUrl($this->logo),
            'photos'        => $photoUrls,
            'province'      => $this->province,
            'city'          => $this->city,
            'address'       => $this->address,
            'phone'         => $this->phone,
            'whatsapp'      => $this->whatsapp,
            'email'         => $this->email,
            'is_app_chat'   => (bool)$this->is_app_chat,
            'status'        => $this->status,
            'created_at'    => $this->created_at,
            'time_ago'      => $this->created_at ? $this->created_at->diffForHumans() : null,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'distance'      => isset($this->distance) ? round($this->distance, 2) : null,
            'business_hours' => $this->relationLoaded('businessHours') && $this->businessHours ? [
                'sunday'    => $this->businessHours->sunday,
                'monday'    => $this->businessHours->monday,
                'tuesday'   => $this->businessHours->tuesday,
                'wednesday' => $this->businessHours->wednesday,
                'thursday'  => $this->businessHours->thursday,
                'friday'    => $this->businessHours->friday,
                'saturday'  => $this->businessHours->saturday,
            ] : null,
        ];
    }   

    /**
     * Safely resolve image URL
     */
    private function getImageUrl($path)
    {
        if (!$path) {
            return null;
        }
        try {
            return helpers::generateTempURL($path, config('app.file_system'));
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk(config('app.file_system'))->url($path);
        }
    }
}
