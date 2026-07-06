<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $conversation = Conversation::find($chatId);
    if (!$conversation) {
        return false;
    }
    return (int) $user->id === (int) $conversation->sender_id || (int) $user->id === (int) $conversation->receiver_id;
});