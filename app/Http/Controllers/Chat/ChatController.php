<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return inertia()->render('Users', [
            'users' => User::query()->where('id', '!=', auth()->id())->get(),
        ]);
        // return Inertia::render('Users');
    }
}
