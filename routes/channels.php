<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('chatroom.{chatroomId}', function ($user, $chatroomId) {
    return $user->chatrooms()->where('chatroom_id', $chatroomId)->exists();
});
