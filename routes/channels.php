<?php

use App\Models\Message;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('private.message.{id1}.{id2}', function ($user, $id1, $id2) {
    return in_array($user->id, [$id1, $id2]);
});

Broadcast::channel('presence.chatroom', function ($user) {
    // $newMessages = Message::query()
    //     ->where('from_u', $user->id)
    //     ->where('to_u', 4)
    //     ->where('is_read', false)
    //     ->count();
    $newMessages = 0;
    return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'newMessages' => $newMessages, 'auth' => auth()->id()];
    // return $user->only('id', 'name', 'email', 'newMessages');
});