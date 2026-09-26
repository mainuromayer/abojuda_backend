<?php

namespace App\Http\Resources\Api\V1\Post;

use App\Helpers\helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostCardResource extends JsonResource
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

        $locale = $request->get('lang') ?? $request->header('lang') ?? (substr($request->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        $categorySlug = $this->category?->slug ?? null;
        $title = null;
        if ($categorySlug === 'business-directory' || $this->category_id == 3) {
            $title = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('business_name', $locale, false) : null)
                ?: ((method_exists($this->resource, 'getTranslation') ? $this->getTranslation('company_name', $locale, false) : null)
                ?: ($this->business_name ?: $this->company_name));
        } else {
            $title = (method_exists($this->resource, 'getTranslation') ? $this->getTranslation('title', $locale, false) : null)
                ?: $this->title;
        }

        if (!empty($title)) {
            if ($locale === 'ar' && !preg_match('/\p{Arabic}/u', $title)) {
                $title = \App\Helpers\helpers::translateText($title, 'ar');
            } elseif ($locale === 'en' && preg_match('/\p{Arabic}/u', $title)) {
                $title = \App\Helpers\helpers::translateText($title, 'en');
            }
        }

        $category = $this->category;
        $categoryName = null;
        if ($category) {
            $categoryName = (method_exists($category, 'getTranslation') ? $category->getTranslation('name', $locale, false) : null) ?: $category->name;
            if ($locale === 'ar' && !preg_match('/\p{Arabic}/u', $categoryName)) {
                $categoryName = \App\Helpers\helpers::translateText($categoryName, 'ar');
            }
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

        $price = $this->price ?? null;
        if ($price !== null) {
            $price = str_replace(
                ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩', '٫'],
                ['0','1','2','3','4','5','6','7','8','9', '.'],
                (string)$price
            );
        }

        $data = [
            'id'            => $this->id,
            'category_id'   => $this->category_id,
            'category_name' => $categoryName,
            'category_slug' => $categorySlug,
            'post_type'     => $categoryName,
            'title'         => $title,
            'thumbnail'     => $this->getImageUrl($this->thumbnail),
            'price'         => $price,
            'province'      => $loc['province'] ?? null,
            'city'          => $loc['city'] ?? null,
            'address'       => $address,
            'full_location' => $fullLocation,
            'is_featured'   => (bool)$this->is_featured,
            'is_wish'       => (bool)$this->is_wish,
            'time_ago'      => $this->created_at ? $this->created_at->diffForHumans() : null,
            'distance'      => isset($this->distance) ? round($this->distance, 2) : null,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
        ];

        return $data;
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
        } catch (Exception $e) {
            return Storage::disk(config('app.file_system'))->url($path);
        }
    }
}
