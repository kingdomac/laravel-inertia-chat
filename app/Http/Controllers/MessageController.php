<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

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

    public function markeMessagesAsRead(User $user): JsonResponse
    {
        Message::query()
            ->where('from_u', $user->id)
            ->where('to_u', auth()->id())
            ->update(['is_read' => true]);

        return response()->json();
    }

    public function countUsersNewMessages(): JsonResponse
    {
        request()->validate([
            'usersId' => ['required', 'array'],
            'usersId.*' =>
            [Rule::exists('users', 'id')],
        ]);

        $users =  User::query()->withCount(['messages' => function (Builder $query) {
            $query->where('to_u', auth()->id())
                ->where('is_read', false);
        }])
            ->whereIn('id', request('usersId'))
            ->get()->pluck('messages_count', 'id');

        return Response::json($users);
    }

    public function countUserNewMessages(User $user): JsonResponse
    {
        $user = User::query()->withCount(['messages' => function (Builder $query) {
            $query->where('to_u', auth()->id())
                ->where('is_read', false);
        }])
            ->where('id', $user->id)
            ->get()->pluck('messages_count', 'id');
        return Response::json($user);
    }
}
