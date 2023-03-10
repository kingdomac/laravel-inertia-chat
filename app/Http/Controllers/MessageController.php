<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;

class MessageController extends Controller
{
    public function getUserMessages(User $user): JsonResponse
    {
        return Response::json(
            Message::query()
                ->whereIn('from_u', [auth()->id(), $user->id])
                ->whereIn('to_u', [auth()->id(), $user->id])
                ->orderBy('id', 'asc')
                ->get()
        );
    }

    public function markeMessagesAsRead(): JsonResponse
    {
        Message::query()
            ->where('from_u', request('user'))
            ->where('to_u', auth()->id())
            ->update(['is_read' => true]);
        return response()->json();
    }

    public function countUsersNewMessages(): JsonResponse
    {
        $users =  User::query()->withCount(['messages' => function (Builder $query) {
            $query->where('to_u', auth()->id())
                ->where('is_read', false);
        }])
            ->whereIn('id', request('usersId'))
            ->get()->pluck('messages_count', 'id');

        return Response::json($users);
    }

    public function countUserNewMessages($userId)
    {
        $user = User::query()->withCount(['messages' => function (Builder $query) {
            $query->where('to_u', auth()->id())
                ->where('is_read', false);
        }])
            ->where('id', $userId)
            ->get()->pluck('messages_count', 'id');
        return Response::json($user);
    }
}
