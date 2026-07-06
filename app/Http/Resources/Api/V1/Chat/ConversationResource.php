<?php

namespace App\Http\Resources\Api\V1\Chat;

use App\Helpers\helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = auth('api')->id();
        $otherUser = $this->sender_id === $userId ? $this->receiver : $this->sender;

        // Fallback in case user was deleted
        $otherUserData = null;
        if ($otherUser) {
            $otherUserData = [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar' => $this->getImageUrl($otherUser->avatar),
                'is_online' => true, // Mock online status for demo/UI matching
            ];
        }

        $postData = null;
        if ($this->post) {
            $postData = [
                'id' => $this->post->id,
                'title' => $this->post->title,
                'price' => $this->post->price,
                'thumbnail' => $this->getImageUrl($this->post->thumbnail),
            ];
        }

        $lastMsg = $this->lastMessage;
        $lastMsgData = null;
        if ($lastMsg) {
            $lastMsgData = [
                'id' => $lastMsg->id,
                'message' => $lastMsg->message,
                'sender_id' => $lastMsg->sender_id,
                'created_at' => $lastMsg->created_at,
                'time_ago' => $this->formatTimeAgo($lastMsg->created_at),
            ];
        }

        return [
            'id' => $this->id,
            'other_user' => $otherUserData,
            'post' => $postData,
            'last_message' => $lastMsgData,
            'unread_count' => $this->unreadMessagesCount($userId),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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

    private function formatTimeAgo($date)
    {
        if (!$date) return null;
        $date = Carbon::parse($date);
        if ($date->isToday()) {
            return $date->format('g:i A');
        }
        if ($date->isYesterday()) {
            return 'Yesterday';
        }
        if ($date->diffInDays() < 7) {
            return $date->format('l');
        }
        return $date->diffForHumans();
    }
}
