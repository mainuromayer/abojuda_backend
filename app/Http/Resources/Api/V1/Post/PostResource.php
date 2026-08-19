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

        $lang = $request->header('Accept-Language', 'en');
        $translatedTitle = $this->title ? \App\Helpers\translateHelper::translateCached("post.title.{$this->id}", $this->title, $lang) : null;
        $translatedDescription = $this->description ? \App\Helpers\translateHelper::translateCached("post.description.{$this->id}", $this->description, $lang) : null;
        $translatedCompanyName = $this->company_name ? \App\Helpers\translateHelper::translateCached("post.company_name.{$this->id}", $this->company_name, $lang) : null;
        $translatedBusinessName = $this->business_name ? \App\Helpers\translateHelper::translateCached("post.business_name.{$this->id}", $this->business_name, $lang) : null;
        $translatedBusinessCategory = $this->business_category ? \App\Helpers\translateHelper::translateCached("post.business_category.{$this->id}", $this->business_category, $lang) : null;

        $categoryName = $this->whenLoaded('category', fn() => $this->category?->name, $this->category?->name ?? null);
        $translatedCategoryName = $categoryName ? \App\Helpers\translateHelper::translateCached("category.name.{$this->category_id}", $categoryName, $lang) : null;
        $categoryLower = strtolower($categoryName ?? '');
        $categorySlug = $this->category?->slug ?? null;

        $loc = \App\Helpers\translateHelper::translateLocation($this->province, $this->city, $lang);

        if ($categorySlug === 'buy-sell') {
            return [
                'user_id'       => $this->user_id,
                'user_name'     => $this->user?->name,
                'user_since'    => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'   => $this->category_id,
                'category_name' => $translatedCategoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'photos'        => $photoUrls,
                'title'         => $translatedTitle,
                'description'   => $translatedDescription,
                'price'         => $this->price,
                'condition'     => $this->condition,
                'province'      => $loc['province'],
                'city'          => $loc['city'],
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
                'category_name' => $translatedCategoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'title'         => $translatedTitle,
                'company_name'  => $translatedCompanyName,
                'description'   => $translatedDescription,
                'job_type'      => $this->job_type,
                'province'      => $loc['province'],
                'city'          => $loc['city'],
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
                'category_name'     => $translatedCategoryName,
                'category_slug'     => $categorySlug,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'business_name'     => $translatedBusinessName,
                'business_category' => $translatedBusinessCategory,
                'description'       => $translatedDescription,
                'website'           => $this->website,
                'photos'            => $photoUrls,
                'province'          => $loc['province'],
                'city'              => $loc['city'],
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
                'category_name' => $translatedCategoryName,
                'category_slug' => $categorySlug,
                'thumbnail'     => $this->getImageUrl($this->thumbnail),
                'photos'        => $photoUrls,
                'title'         => $translatedTitle,
                'description'   => $translatedDescription,
                'service_area'  => $this->service_area,
                'province'      => $loc['province'],
                'city'          => $loc['city'],
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
            'category_name' => $translatedCategoryName,
            'category_slug' => $categorySlug,
            'thumbnail'     => $this->getImageUrl($this->thumbnail),
            'title'         => $translatedTitle,
            'business_name' => $translatedBusinessName,
            'company_name'  => $translatedCompanyName,
            'business_category' => $translatedBusinessCategory,
            'description'   => $translatedDescription,
            'price'         => $this->price,
            'condition'     => $this->condition,
            'job_type'      => $this->job_type,
            'service_area'  => $this->service_area,
            'website'       => $this->website,
            'logo'          => $this->getImageUrl($this->logo),
            'photos'        => $photoUrls,
            'province'      => $loc['province'],
            'city'          => $loc['city'],
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
