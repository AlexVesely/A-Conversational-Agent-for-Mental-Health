<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnhancedChatController;
use App\Http\Controllers\BaselineChatController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/chat1', [EnhancedChatController::class, 'index'])->name('chat1.index'); // Show UI
Route::post('/chat1', [EnhancedChatController::class, 'handle'])->name('chat1.handle'); // Process Input

Route::get('/chat2', [BaselineChatController::class, 'index'])->name('chat2.index'); // Show UI
Route::post('/chat2', [BaselineChatController::class, 'handle'])->name('chat2.handle'); // Process Input
