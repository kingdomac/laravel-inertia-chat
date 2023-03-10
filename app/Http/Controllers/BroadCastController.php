<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\ChatRoomEvent;
use App\Events\PrivateMessageEvent;

class BroadCastController extends Controller
{
    public function broadcastToRoom()
    {
        ChatRoomEvent::dispatch(request('message'));
    }

    public function broadcastOneToOneMsg()
    {
        Message::create([
            'from_u' => auth()->id(),
            'to_u' => request('user'),
            'body' => request('message'),
            'is_read' => false,
        ]);
        PrivateMessageEvent::dispatch(request('user'), request('message'));
    }
}