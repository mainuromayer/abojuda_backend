<?php

namespace App\Http\Resources\Api\V1\DynamicPage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicPageDetailsResource extends JsonResource
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
            'title' => \App\Helpers\translateHelper::translateCached("dynamic_page.title.{$this->id}", $this->title ?? '', \Illuminate\Support\Facades\App::getLocale()),
            'slug' => $this->slug,
            'content' => \App\Helpers\translateHelper::translateCached("dynamic_page.content.{$this->id}", $this->content ?? '', \Illuminate\Support\Facades\App::getLocale())
        ];
    }
}
