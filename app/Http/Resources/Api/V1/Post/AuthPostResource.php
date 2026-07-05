<?php

namespace App\Http\Resources\Api\V1\Post;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AuthPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $thumbUrl = $this->thumbnail ? helpers::generateTempURL($this->thumbnail, config('app.file_system')) : null;
        if (!$thumbUrl && $this->thumbnail) {
            $thumbUrl = Storage::disk(config('app.file_system'))->url($this->thumbnail);
        }

        $status = strtolower($this->status);
        $timeAgo = null;
        $submitDate = $this->created_at ? $this->created_at->format('M d, Y') : null;

        if ($status === 'active' || $status === 'approved') {
            $timeAgo = $this->created_at ? $this->created_at->diffForHumans() : null;
        }

        $data = [
            'id'            => $this->id,
            'thumbnail'     => $thumbUrl,
            'status'        => $this->status,
            'time_ago'      => $timeAgo,
            'submit_date'   => $submitDate,
            'total_view'    => (int) $this->views,
            'total_wish'    => (int) $this->wishes()->count(),
            'total_message' => 0, // Defaults to 0 since no chat/message tables exist
        ];

        if ($this->category_id == 3 && empty($this->title)) {
            $data['business_name'] = $this->business_name;
        } else {
            $data['title'] = $this->title;
        }

        return $data;
    }
}
