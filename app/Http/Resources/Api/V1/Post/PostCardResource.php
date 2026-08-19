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

        $categorySlug = $this->category?->slug ?? null;
        $title = $this->title ?? null;
        if ($categorySlug === 'business-directory' || $this->category_id == 3) {
            $title = $this->business_name ?? ($this->company_name ?? null);
        }

        $locale = $request->header('Accept-Language', 'en');
        $translatedTitle = $title ? \App\Helpers\translateHelper::translateCached("post.title.{$this->id}", $title, $locale) : null;
        $categoryName = $this->whenLoaded('category', fn() => $this->category?->name, $this->category?->name ?? null);
        $translatedCategoryName = $categoryName ? \App\Helpers\translateHelper::translateCached("category.name.{$this->category_id}", $categoryName, $locale) : null;

        $loc = \App\Helpers\translateHelper::translateLocation($this->province, $this->city, $locale);

        $data = [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $translatedCategoryName,
            'title' => $translatedTitle,
            'thumbnail' => $this->getImageUrl($this->thumbnail),
            'price' => $this->price ?? null,
            'province' => $loc['province'] ?? null,
            'city' => $loc['city'] ?? null,
            'is_featured' => (bool)$this->is_featured,
            'is_wish' => (bool)$this->is_wish,
            'time_ago' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'distance' => isset($this->distance) ? round($this->distance, 2) : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
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
