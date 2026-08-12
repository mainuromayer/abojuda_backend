<?php

namespace App\Http\Resources\Api\V1\Ad;

use App\Helpers\helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'media' => $this->getMediaUrl($this->media),
            'media_type' => $this->media_type,
            'back_link' => $this->back_link,
        ];
    }

    private function getMediaUrl($path)
    {
        if (!$path) {
            return null;
        }
        try {
            $url = helpers::generateTempURL($path, config('app.file_system'));
            if ($url) {
                return $url;
            }
            return asset('storage/' . $path);
        } catch (Exception $e) {
            return asset('storage/' . $path);
        }
    }
}
