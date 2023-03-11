<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\ChatRoomEvent;
use Illuminate\Validation\Rule;
use App\Events\PrivateMessageEvent;

class BroadCastController extends Controller
{
    public function broadcastToRoom()
    {
        request()->validate([
            'message' => ['required', 'string']
        ]);

        ChatRoomEvent::dispatch(request('message'));
    }

    public function broadcastOneToOneMsg()
    {
        request()->validate([
            'user' => ['required', 'integer', Rule::exists('users', 'id')],
            'message' => ['required', 'string']
        ]);

        Message::create([
            'from_u' => auth()->id(),
            'to_u' => request('user'),
            'body' => request('message'),
            'is_read' => false,
        ]);
        PrivateMessageEvent::dispatch(request('user'), request('message'));
    }
}
