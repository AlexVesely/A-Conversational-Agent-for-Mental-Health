<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BaselineChatController extends Controller
{
    public function index()
    {
        return view('chat2', ['response' => session('response')]);
    }

    public function handle(Request $request)
    {
        // Validation: Ensure message is valid
        $request->validate(
            ['message' => 'required|string|max:1000|min:10'], // checking message exists, is a string and less than 1000 chars
            [
                'message.required' => 'Please enter a message.',
                'message.max'      => 'Your message is too long. Please keep it under 1000 characters.',
                'message.min'      => 'Your message is too short. Please enter more than 10 characters.'
            ]        
        );

        // IF the validation passes, move on to the code underneath

        $userMessage = $request->input('message'); // Retreives input from 'message form'

        // SKIP Safety Filter

        // SKIP Emotion Classification

        // Build Prompt. The baseline creates a simple prompt with no CBT strategy
        $llmPrompt = "The user says: \"$userMessage\". Please provide a helpful, direct and complete 
                        response. The user cannot reply to you so do not expect further interaction.";
        
        // Get LLM response
        $botReply = $this->callLlm($llmPrompt);
        
        // Send back what the user said and the bot reply
        return $this->redirectWithResponse($userMessage, $botReply);
    }

    private function callLlm(string $fullPrompt): string
    {
        $url = "https://router.huggingface.co/v1/chat/completions";

        $response = Http::withToken(env('HF_TOKEN'))
            ->timeout(60) // Allow up to 60 seconds for LLM to write its response
            ->post($url, [
                "model" => "meta-llama/Llama-3.1-8B-Instruct", 
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $fullPrompt // Prompt that was generated earlier
                    ]
                ],
                "max_tokens" => 500, // Limits length of AI reply
                "stream" => false // Receive AI response all at once rather than word by word
            ]);

        if ($response->failed()) {
            // Writes error in storage/logs/laravel.log
            \Log::error('HF LLM Error: ' . $response->status() . ' - ' . $response->body());
            return "I'm having a little trouble connecting to the LLM. Try again?";
        }

        $result = $response->json(); //Transform text into php array

        // Format is choices -> message -> content to find the AI response
        return $result['choices'][0]['message']['content'];
    }

    // Helper to handle the redirect and session storage
    private function redirectWithResponse(string $userMsg, string $botMsg)
    {
        return redirect()->route('chat2.index')->with([
            'user_message' => $userMsg,
            'bot_response' => $botMsg
        ]);
    }
}
