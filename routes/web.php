<?php

use App\Http\Controllers\BroadCastController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return inertia()->render('Chat/ChatRoom');
    })->name('dashboard');


    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile',  'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::controller(MessageController::class)->group(function () {
        Route::get('/messages/{user}',  'getUserMessages');
        Route::put('/mark-as-read-messages/{user}', 'markeMessagesAsRead');
        Route::post('/count-new-messages', 'countUserNewMessages');
    });


    Route::controller(BroadCastController::class)->group(function () {
        Route::post('/broadcast-room-message', 'broadcastToRoom');
        Route::post('/broadcast-private-message', 'broadcastOneToOneMsg')->name('private.message');
    });
});

require __DIR__ . '/auth.php';