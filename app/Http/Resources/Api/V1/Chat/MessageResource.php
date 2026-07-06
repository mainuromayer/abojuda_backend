<?php

namespace App\Http\Resources\Api\V1\Chat;

use App\Helpers\helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = auth('api')->id();
        $senderName = $this->sender_id === $userId ? 'You' : ($this->sender?->name ?? 'User');

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $senderName,
            'message' => $this->message,
            'attachment' => $this->getImageUrl($this->attachment),
            'is_read' => (bool)$this->is_read,
            'created_at' => $this->created_at,
            'time_ago' => $this->formatMessageTime($this->created_at),
        ];
    }

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

    private function formatMessageTime($date)
    {
        if (!$date) return null;
        $date = Carbon::parse($date);
        return $date->format('M d, g:i A'); // e.g., Oct 15, 9:42 AM
    }
}
