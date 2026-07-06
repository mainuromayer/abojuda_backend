<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Chat\GetMessagesRequest;
use App\Http\Requests\Api\V1\Chat\MarkReadRequest;
use App\Http\Requests\Api\V1\Chat\SendMessageRequest;
use App\Http\Resources\Api\V1\Chat\ConversationResource;
use App\Http\Resources\Api\V1\Chat\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    use ApiResponse;

    /**
     * Get list of conversations for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $userId = $user->id;
            $blockedUserIds = $user->getBlockedUserIds();

            $query = Conversation::where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            });

            // Exclude conversations with blocked users
            if (!empty($blockedUserIds)) {
                $query->whereNotIn('sender_id', $blockedUserIds)
                      ->whereNotIn('receiver_id', $blockedUserIds);
            }

            // Optional search by participant name
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($userId, $searchTerm) {
                    $q->whereHas('sender', function ($sub) use ($userId, $searchTerm) {
                        $sub->where('id', '!=', $userId)
                            ->where('name', 'like', '%' . $searchTerm . '%');
                    })->orWhereHas('receiver', function ($sub) use ($userId, $searchTerm) {
                        $sub->where('id', '!=', $userId)
                            ->where('name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }

            // Sort by latest activity (updated_at)
            $conversations = $query->with(['sender', 'receiver', 'post', 'lastMessage'])
                                   ->orderBy('updated_at', 'desc')
                                   ->get();

            return $this->sendResponse(ConversationResource::collection($conversations), 'Conversations retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve conversations.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages(GetMessagesRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $userId = $user->id;
            $conversationId = $request->input('conversation_id');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return $this->sendError('Conversation not found.', [], 404);
            }

            // Ensure the user belongs to this conversation
            if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
                return $this->sendError('Unauthorized access to conversation.', [], 403);
            }

            // Exclude if with blocked user
            $blockedUserIds = $user->getBlockedUserIds();
            if (in_array($conversation->sender_id, $blockedUserIds) || in_array($conversation->receiver_id, $blockedUserIds)) {
                return $this->sendError('You cannot chat with this user.', [], 403);
            }

            // Mark unread messages sent by the other user as read
            $unreadMessages = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->get();

            foreach ($unreadMessages as $msg) {
                $msg->is_read = true;
                $msg->save();
            }

            // Get messages ordered by created_at asc
            $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

            return $this->sendResponse(MessageResource::collection($messages), 'Messages retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve messages.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a message (creates a conversation if one doesn't exist yet).
     */
    public function sendMessage(SendMessageRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $senderId = $user->id;
            $receiverId = (int)$request->input('receiver_id');
            $postId = $request->input('post_id');

            // Cannot message yourself
            if ($senderId === $receiverId) {
                return $this->sendError('You cannot message yourself.', [], 400);
            }

            // Verify not blocked
            $blockedUserIds = $user->getBlockedUserIds();
            if (in_array($receiverId, $blockedUserIds)) {
                return $this->sendError('You cannot message this user.', [], 403);
            }

            // Find or create conversation
            $conversation = Conversation::where(function ($q) use ($senderId, $receiverId) {
                $q->where(function($q1) use ($senderId, $receiverId) {
                    $q1->where('sender_id', $senderId)->where('receiver_id', $receiverId);
                })->orWhere(function($q2) use ($senderId, $receiverId) {
                    $q2->where('sender_id', $receiverId)->where('receiver_id', $senderId);
                });
            })
            ->where('post_id', $postId)
            ->first();

            if (!$conversation) {
                // Object instantiation & saving instead of create()
                $conversation = new Conversation();
                $conversation->post_id = $postId;
                $conversation->sender_id = $senderId;
                $conversation->receiver_id = $receiverId;
                $conversation->save();
            }

            // Process attachment if any
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_chat_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $attachmentPath = helpers::uploadFile('chat', $file, $fileName, config('app.file_system'));
            }

            // Create message using Object instantiation & saving instead of create()
            $message = new Message();
            $message->conversation_id = $conversation->id;
            $message->sender_id = $senderId;
            $message->message = $request->input('message');
            $message->attachment = $attachmentPath;
            $message->is_read = false;
            $message->save();

            // Touch conversation to update its updated_at timestamp
            $conversation->touch();

            // Broadcast the message to other participants
            $message->load('sender');
            broadcast(new MessageSent($message))->toOthers();

            // Send Firebase push notification to the receiver
            $receiverId = ($conversation->sender_id === $senderId) ? $conversation->receiver_id : $conversation->sender_id;
            $receiver = User::find($receiverId);
            if ($receiver) {
                $tokens = $receiver->firebaseTokens()->where('is_active', true)->pluck('token')->toArray();
                if (!empty($tokens)) {
                    $notificationData = [
                        'title' => $user->name ?? 'New Message',
                        'message' => $message->message ?? 'Sent an attachment',
                        'data' => [
                            'type' => 'chat',
                            'conversation_id' => (string)$conversation->id,
                            'sender_id' => (string)$senderId,
                        ]
                    ];
                    foreach ($tokens as $token) {
                        helpers::sendNotifyMobile($token, $notificationData);
                    }
                }
            }

            return $this->sendResponse(new MessageResource($message), 'Message sent successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to send message.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a conversation messages as read.
     */
    public function markAsRead(MarkReadRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $userId = $user->id;
            $conversationId = $request->input('conversation_id');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return $this->sendError('Conversation not found.', [], 404);
            }

            if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
                return $this->sendError('Unauthorized access to conversation.', [], 403);
            }

            $unreadMessages = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->get();

            foreach ($unreadMessages as $msg) {
                $msg->is_read = true;
                $msg->save();
            }

            // Broadcast that the messages were read
            broadcast(new MessagesRead($conversation->id, $userId))->toOthers();

            return $this->sendResponse(null, 'Conversation marked as read.');
        } catch (Exception $e) {
            return $this->sendError('Failed to mark conversation as read.', ['error' => $e->getMessage()], 500);
        }
    }
}
