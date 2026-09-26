<?php

namespace App\Http\Resources\Api\V1\Post;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
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

        $lang = $request->get('lang') ?? $request->header('lang') ?? (substr($request->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'ar';
        }

        // Title
        $title = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('title', $lang, false) : null) ?: $this->title;
        if (!empty($title)) {
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $title)) {
                $title = helpers::translateText($title, 'ar');
            } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $title)) {
                $title = helpers::translateText($title, 'en');
            }
        }

        // Description
        $description = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('description', $lang, false) : null) ?: $this->description;
        if (!empty($description)) {
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', strip_tags($description))) {
                $description = helpers::translateText($description, 'ar');
            } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', strip_tags($description))) {
                $description = helpers::translateText($description, 'en');
            }
        }

        // Company Name
        $companyName = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('company_name', $lang, false) : null) ?: $this->company_name;
        if (!empty($companyName)) {
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $companyName)) {
                $companyName = helpers::translateText($companyName, 'ar');
            } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $companyName)) {
                $companyName = helpers::translateText($companyName, 'en');
            }
        }

        // Business Name
        $businessName = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('business_name', $lang, false) : null) ?: $this->business_name;
        if (!empty($businessName)) {
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $businessName)) {
                $businessName = helpers::translateText($businessName, 'ar');
            } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $businessName)) {
                $businessName = helpers::translateText($businessName, 'en');
            }
        }

        // Business Category
        $businessCategory = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('business_category', $lang, false) : null) ?: $this->business_category;
        if (!empty($businessCategory)) {
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $businessCategory)) {
                $businessCategory = helpers::translateText($businessCategory, 'ar');
            } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $businessCategory)) {
                $businessCategory = helpers::translateText($businessCategory, 'en');
            }
        }

        // Category Name
        $category = $this->category;
        $categoryName = null;
        if ($category) {
            $categoryName = (method_exists($category, 'getTranslation') ? $category->getTranslation('name', $lang, false) : null) ?: $category->name;
            if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $categoryName)) {
                $categoryName = helpers::translateText($categoryName, 'ar');
            }
        }
        $categorySlug = $this->category?->slug ?? null;

        // Location & Address
        $loc = translateHelper::translateLocation($this->province, $this->city, $lang);
        $address = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('address', $lang, false) : null) ?: $this->address;
        if (empty($address) && !empty($loc['province'])) {
            $address = ($lang === 'ar')
                ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
                : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));
        }
        $fullLocation = ($lang === 'ar')
            ? implode('، ', array_filter([$loc['city'], $loc['province'], 'كندا']))
            : implode(', ', array_filter([$loc['city'], $loc['province'], 'Canada']));

        // Job type, condition, service area
        $translatedJobType = translateHelper::translateJobType($this->job_type, $lang);
        $translatedCondition = translateHelper::translateCondition($this->condition, $lang);
        $translatedServiceArea = translateHelper::translateServiceArea($this->service_area, $lang);

        // Price (keep strictly in standard English/Western numerals, no Arabic conversion)
        $price = $this->price;
        if ($price !== null) {
            $price = str_replace(
                ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩', '٫'],
                ['0','1','2','3','4','5','6','7','8','9', '.'],
                (string)$price
            );
        }

        if ($categorySlug === 'buy-sell') {
            return [
                'user_id'           => $this->user_id,
                'user_name'         => $this->user?->name,
                'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'       => $this->category_id,
                'category_name'     => $categoryName,
                'category_slug'     => $categorySlug,
                'post_type'         => $categoryName,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'photos'            => $photoUrls,
                'title'             => $title,
                'description'       => $description,
                'price'             => $price,
                'condition'         => $translatedCondition,
                'province'          => $loc['province'],
                'city'              => $loc['city'],
                'address'           => $address,
                'full_location'     => $fullLocation,
                'phone'             => $this->phone,
                'whatsapp'          => $this->whatsapp,
                'email'             => $this->email,
                'is_app_chat'       => (bool)$this->is_app_chat,
                'status'            => $this->status,
                'created_at'        => $this->created_at,
                'time_ago'          => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'          => $this->latitude,
                'longitude'         => $this->longitude,
                'distance'          => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        } elseif ($categorySlug === 'jobs') {
            return [
                'user_id'           => $this->user_id,
                'user_name'         => $this->user?->name,
                'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'       => $this->category_id,
                'category_name'     => $categoryName,
                'category_slug'     => $categorySlug,
                'post_type'         => $categoryName,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'title'             => $title,
                'company_name'      => $companyName,
                'description'       => $description,
                'job_type'          => $translatedJobType,
                'job_specification' => $translatedJobType,
                'province'          => $loc['province'],
                'city'              => $loc['city'],
                'address'           => $address,
                'full_location'     => $fullLocation,
                'phone'             => $this->phone,
                'whatsapp'          => $this->whatsapp,
                'email'             => $this->email,
                'is_app_chat'       => (bool)$this->is_app_chat,
                'status'            => $this->status,
                'created_at'        => $this->created_at,
                'time_ago'          => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'          => $this->latitude,
                'longitude'         => $this->longitude,
                'distance'          => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        } elseif ($categorySlug === 'business-directory') {
            return [
                'user_id'           => $this->user_id,
                'user_name'         => $this->user?->name,
                'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'       => $this->category_id,
                'category_name'     => $categoryName,
                'category_slug'     => $categorySlug,
                'post_type'         => $categoryName,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'business_name'     => $businessName,
                'business_category' => $businessCategory,
                'description'       => $description,
                'website'           => $this->website,
                'photos'            => $photoUrls,
                'province'          => $loc['province'],
                'city'              => $loc['city'],
                'address'           => $address,
                'full_location'     => $fullLocation,
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
                'user_id'           => $this->user_id,
                'user_name'         => $this->user?->name,
                'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
                'category_id'       => $this->category_id,
                'category_name'     => $categoryName,
                'category_slug'     => $categorySlug,
                'post_type'         => $categoryName,
                'thumbnail'         => $this->getImageUrl($this->thumbnail),
                'photos'            => $photoUrls,
                'title'             => $title,
                'description'       => $description,
                'service_area'      => $translatedServiceArea,
                'province'          => $loc['province'],
                'city'              => $loc['city'],
                'address'           => $address,
                'full_location'     => $fullLocation,
                'phone'             => $this->phone,
                'whatsapp'          => $this->whatsapp,
                'email'             => $this->email,
                'is_app_chat'       => (bool)$this->is_app_chat,
                'status'            => $this->status,
                'created_at'        => $this->created_at,
                'time_ago'          => $this->created_at ? $this->created_at->diffForHumans() : null,
                'latitude'          => $this->latitude,
                'longitude'         => $this->longitude,
                'distance'          => isset($this->distance) ? round($this->distance, 2) : null,
            ];
        }

        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'user_name'         => $this->user?->name,
            'user_since'        => $this->user?->created_at ? (string)$this->user->created_at->format('Y') : null,
            'category_id'       => $this->category_id,
            'category_name'     => $categoryName,
            'category_slug'     => $categorySlug,
            'post_type'         => $categoryName,
            'thumbnail'         => $this->getImageUrl($this->thumbnail),
            'title'             => $title,
            'business_name'     => $businessName,
            'company_name'      => $companyName,
            'business_category' => $businessCategory,
            'description'       => $description,
            'price'             => $price,
            'condition'         => $translatedCondition,
            'job_type'          => $translatedJobType,
            'job_specification' => $translatedJobType,
            'service_area'      => $translatedServiceArea,
            'website'           => $this->website,
            'logo'              => $this->getImageUrl($this->logo),
            'photos'            => $photoUrls,
            'province'          => $loc['province'],
            'city'              => $loc['city'],
            'address'           => $address,
            'full_location'     => $fullLocation,
            'phone'             => $this->phone,
            'whatsapp'          => $this->whatsapp,
            'email'             => $this->email,
            'is_app_chat'       => (bool)$this->is_app_chat,
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
