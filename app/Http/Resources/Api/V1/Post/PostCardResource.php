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

        $data = [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn() => $this->category?->name, $this->category?->name ?? null),
            'title' => $title,
            'thumbnail' => $this->getImageUrl($this->thumbnail),
            'price' => $this->price ?? null,
            'province' => $this->province ?? null,
            'city' => $this->city ?? null,
            'is_featured' => (bool)$this->is_featured,
            'is_wish' => (bool)$this->is_wish,
            'time_ago' => $this->created_at ? $this->created_at->diffForHumans() : null,
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
