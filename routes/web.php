<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnhancedChatController;
use App\Http\Controllers\BaselineChatController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/chatA', [EnhancedChatController::class, 'index'])->name('chatA.index'); // Show UI
Route::post('/chatA', [EnhancedChatController::class, 'handle'])->name('chatA.handle'); // Process Input

Route::get('/chatB', [BaselineChatController::class, 'index'])->name('chatB.index'); // Show UI
Route::post('/chatB', [BaselineChatController::class, 'handle'])->name('chatB.handle'); // Process Input
