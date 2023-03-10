<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return inertia()->render('Users', [
            'users' => User::query()->where('id', '!=', auth()->id())->get(),
        ]);
        // return Inertia::render('Users');
    }
}
