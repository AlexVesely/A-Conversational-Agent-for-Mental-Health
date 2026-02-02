<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index'); // Show UI
Route::post('/chat', [ChatController::class, 'handle'])->name('chat.handle'); // Process Input

Route::get('/hf-test', function () {
    $url = "https://router.huggingface.co/hf-inference/models/j-hartmann/emotion-english-distilroberta-base";

    $response = Http::withToken(env('HF_TOKEN'))
        ->post($url, [
            'inputs' => 'I am sad today',
        ]);

    return $response->json();
});

Route::get('/hf-test-2', function (Request $request) {
    // Sends either this default or follow: localhost/hf-test-2?text=WRITE INPUT HERE
    $inputText = $request->query('text', 'I accidentally forgot to send an input in the URL');

    $url = "https://router.huggingface.co/hf-inference/models/j-hartmann/emotion-english-distilroberta-base";

    $response = Http::withToken(env('HF_TOKEN'))
        ->post($url, [
            'inputs' => $inputText,
            'options' => [
                'use_cache' => false, // Force Hugging Face to re-calculate with new input
                'wait_for_model' => true 
            ]
        ]);

    return response()->json([
        'you_sent' => $inputText, // Debug: verify what was sent
        'api_response' => $response->json()
    ]);
});
