<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Helpers\helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'uuid'     => $this->uuid,
            'name'     => $this->name,
            'email'    => $this->email,
            'province' => $this->province,
            'city'     => $this->city,
            'address'  => $this->address,
            'avatar'   => $this->getImageUrl($this->avatar),
            'role'     => $this->role,
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
        } catch (Exception $e) {
            return Storage::disk(config('app.file_system'))->url($path);
        }
    }
}
