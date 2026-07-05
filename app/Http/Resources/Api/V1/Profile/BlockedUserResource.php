<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $avatarUrl = $this->avatar ? helpers::generateTempURL($this->avatar, config('app.file_system')) : null;
        if (!$avatarUrl && $this->avatar) {
            $avatarUrl = \Illuminate\Support\Facades\Storage::disk(config('app.file_system'))->url($this->avatar);
        }

        // Parse blocked_at and calculate time ago
        $timeAgo = null;
        if (isset($this->blocked_at)) {
            $timeAgo = \Carbon\Carbon::parse($this->blocked_at)->diffForHumans();
        }

        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'avatar'   => $avatarUrl,
            'time_ago' => $timeAgo,
        ];
    }
}
