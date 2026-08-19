<?php

namespace App\Http\Resources\Api\V1\Category;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => \App\Helpers\translateHelper::translateCached("category.name.{$this->id}", $this->name ?? '', \Illuminate\Support\Facades\App::getLocale()),
            'slug' => $this->slug,
            'shot_desc' => \App\Helpers\translateHelper::translateCached("category.shot_desc.{$this->id}", $this->shot_desc ?? '', \Illuminate\Support\Facades\App::getLocale()),
            'icon' => $this->icon ? $this->getImageUrl($this->icon) : null,
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
