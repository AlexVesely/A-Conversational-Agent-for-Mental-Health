<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index'); // Show UI
Route::post('/chat', [ChatController::class, 'handle'])->name('chat.handle'); // Process Input
