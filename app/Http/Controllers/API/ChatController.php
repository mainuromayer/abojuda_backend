<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Helpers\helpers;
use App\Helpers\translateHelper;
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
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('chat.unauthenticated', 'Unauthenticated user.', $lang);
                return $this->sendError($message, [], 401);
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

            $message = translateHelper::translateCached('chat.conversations.success', 'Conversations retrieved successfully.', $lang);

            return $this->sendResponse(ConversationResource::collection($conversations), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('chat.conversations.failed', 'Failed to retrieve conversations.', $lang);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages(GetMessagesRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('chat.unauthenticated', 'Unauthenticated user.', $lang);
                return $this->sendError($message, [], 401);
            }

            $userId = $user->id;
            $conversationId = $request->input('conversation_id');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                $message = translateHelper::translateCached('chat.conversation.not_found', 'Conversation not found.', $lang);
                return $this->sendError($message, [], 404);
            }

            // Ensure the user belongs to this conversation
            if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
                $message = translateHelper::translateCached('chat.conversation.unauthorized', 'Unauthorized access to conversation.', $lang);
                return $this->sendError($message, [], 403);
            }

            // Exclude if with blocked user
            $blockedUserIds = $user->getBlockedUserIds();
            if (in_array($conversation->sender_id, $blockedUserIds) || in_array($conversation->receiver_id, $blockedUserIds)) {
                $message = translateHelper::translateCached('chat.conversation.blocked', 'You cannot chat with this user.', $lang);
                return $this->sendError($message, [], 403);
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
            $messages = $conversation->messages()->with(['sender', 'conversation.post'])->orderBy('created_at', 'asc')->get();

            $message = translateHelper::translateCached('chat.messages.success', 'Messages retrieved successfully.', $lang);

            return $this->sendResponse(MessageResource::collection($messages), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('chat.messages.failed', 'Failed to retrieve messages.', $lang);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a message (creates a conversation if one doesn't exist yet).
     */
    public function sendMessage(SendMessageRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('chat.unauthenticated', 'Unauthenticated user.', $lang);
                return $this->sendError($message, [], 401);
            }

            $senderId = $user->id;
            $receiverId = (int)$request->input('receiver_id');
            $postId = $request->input('post_id');

            // Cannot message yourself
            if ($senderId === $receiverId) {
                $message = translateHelper::translateCached('chat.send.self', 'You cannot message yourself.', $lang);
                return $this->sendError($message, [], 400);
            }

            // Verify not blocked
            $blockedUserIds = $user->getBlockedUserIds();
            if (in_array($receiverId, $blockedUserIds)) {
                $message = translateHelper::translateCached('chat.send.blocked', 'You cannot message this user.', $lang);
                return $this->sendError($message, [], 403);
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
            $newMessage = new Message();
            $newMessage->conversation_id = $conversation->id;
            $newMessage->sender_id = $senderId;
            $newMessage->message = $request->input('message');
            $newMessage->attachment = $attachmentPath;
            $newMessage->is_read = false;
            $newMessage->save();

            // Touch conversation to update its updated_at timestamp
            $conversation->touch();

            // Broadcast the message to other participants
            $newMessage->load(['sender', 'conversation.post']);
            broadcast(new MessageSent($newMessage))->toOthers();

            // Send Firebase push notification to the receiver
            $notifyReceiverId = ($conversation->sender_id === $senderId) ? $conversation->receiver_id : $conversation->sender_id;
            $receiver = User::find($notifyReceiverId);
            if ($receiver) {
                // 1. Create in-app Notification for the receiver
                $postTitle = $conversation->post?->title ? (' about ' . $conversation->post->title) : '';
                $senderName = $user->name ?? 'Someone';
                \App\Models\UserNotification::create([
                    'user_id' => $receiver->id,
                    'type'    => 'new_message',
                    'title'   => 'New Message',
                    'message' => $senderName . ' sent you a message' . $postTitle,
                    'data'    => [
                        'conversation_id' => $conversation->id,
                        'sender_id'       => $senderId,
                        'message_id'      => $newMessage->id,
                    ],
                    'is_read' => false,
                ]);

                // 2. Send Firebase push notification if enabled
                if ($receiver->enable_notification && $receiver->new_message) {
                    $tokens = $receiver->firebaseTokens()->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($tokens)) {
                        $notificationData = [
                            'title' => $user->name ?? 'New Message',
                            'message' => $newMessage->message ?? 'Sent an attachment',
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
            }

            $message = translateHelper::translateCached('chat.send.success', 'Message sent successfully.', $lang);

            return $this->sendResponse(new MessageResource($newMessage), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('chat.send.failed', 'Failed to send message.', $lang);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a conversation messages as read.
     */
    public function markAsRead(MarkReadRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('chat.unauthenticated', 'Unauthenticated user.', $lang);
                return $this->sendError($message, [], 401);
            }

            $userId = $user->id;
            $conversationId = $request->input('conversation_id');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                $message = translateHelper::translateCached('chat.conversation.not_found', 'Conversation not found.', $lang);
                return $this->sendError($message, [], 404);
            }

            if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
                $message = translateHelper::translateCached('chat.conversation.unauthorized', 'Unauthorized access to conversation.', $lang);
                return $this->sendError($message, [], 403);
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

            $message = translateHelper::translateCached('chat.read.success', 'Conversation marked as read.', $lang);

            return $this->sendResponse(null, $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('chat.read.failed', 'Failed to mark conversation as read.', $lang);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }
}