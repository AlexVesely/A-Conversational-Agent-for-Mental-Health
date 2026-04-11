<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EnhancedChatController extends Controller
{
    public function index()
    {
        return view('chat1', ['response' => session('response')]);
    }

    public function handle(Request $request)
    {
        // Validation: Ensure message is valid
        // !!!!!!! CURRENTLY MIN IS SET AS 1 !!!!!!!!!
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

        // Safety Filter
        $flaggedWord = $this->runSafetyFilter($userMessage);
        // if flaggedWord is not null
        if ($flaggedWord) {
            $alert = "[TEST MODE] HIGH RISK LANGUAGE DETECTED: \"$flaggedWord\". NORMAL RESPONSE HALTED.";
            return $this->redirectWithResponse($userMessage, $alert);
        }

        // Emotion Classification
        $apiData = $this->getEmotionData($userMessage);

        // Build Prompt
        $llmPrompt = $this->buildLlmPrompt($userMessage,$apiData);

        // Get LLM response
        $botReply = $this->callLlm($llmPrompt);
        
        // Formatting the Output into String (Temporary for debugging)
        $jsonString = json_encode($apiData);
        $debuggingResponse = "Hello, I see you said: \"$userMessage\". " . 
                 "Detected emotion(s) data: $jsonString" . 
                 "DECISION TREE OUTPUT: $llmPrompt";

        // Send back what the user said and the bot reply
        return $this->redirectWithResponse($userMessage, $botReply);
    }

    //Returns the flagged word if found, otherwise null.
    //?string in type signature indicates either string or null can be returned
    private function runSafetyFilter(string $text): ?string
    {
        $blacklistedWords = ['kill myself', 'suicide', 'suicidal', 'self harm', 'end it all', 'no reason to live'];

        foreach ($blacklistedWords as $word) {
            if (Str::contains(strtolower($text), $word)) {
                return $word;
            }
        }

        return null;
    }

    //Calls the Hugging Face API and returns an array
    private function getEmotionData(string $inputText): array
    {
        $url = "https://router.huggingface.co/hf-inference/models/j-hartmann/emotion-english-distilroberta-base";

        $response = Http::withToken(env('HF_TOKEN'))
            ->timeout(60) // Allow up to 60 seconds for AI response
            ->post($url, [ // Send data to the url
                'inputs' => $inputText,
                'options' => [
                    'use_cache' => false, // Must run calculation on input text
                    'wait_for_model' => true // Allow time for API response
                ]
            ]);

        return [ // return as an array
            'api_response' => $response->json() //format JSON data into a php array
        ];
    }

    //Follows a decision tree to generate a prompt for the llm
    private function buildLlmPrompt(string $userMessage, array $apiData): string 
    {
        $scores = $apiData['api_response'][0];

        $primaryEmotion = $scores[0]; // API returns emotion in order of highest confidence classification
        $secondaryEmotion = $scores[1];

        $primaryLabel = $primaryEmotion['label'];
        $primaryScore = $primaryEmotion['score'];
        $secondaryLabel = $secondaryEmotion['label'];
        $secondaryScore = $secondaryEmotion['score'];

        $specificInstruction = "";

        // Check for High Intensity (80%+)
        if ($primaryScore >= 0.80) {
            $specificInstruction = "The user is feeling intense $primaryLabel. Prioritize immediate de-escalation and grounding.";
        } 
        // Check for Combos 
        elseif ($primaryScore > 0.40 && $secondaryScore > 0.40) {
            $specificInstruction = "The user is experiencing a mix of $primaryLabel and $secondaryLabel . Help them untangle these feelings.";
        }
        // Standard CBT Mapping
        else {
            $specificInstruction = match ($primaryLabel) {
                'anger' => "The user feels angry.",
                'disgust'   => "The user feels disgusted.",
                'fear'    => "The user feels fear.",
                'joy'   => "The user seems joyful." ,
                'neutral'   => "The user seems neutral." ,
                'sadness'   => "The user seems sad." ,
                'surprise'   => "The user seems surprised."
            };
        }

        return "SYSTEM INSTRUCTIONS:
        You are a supportive CBT Chatbot. Follow these core principles:
        - Help the user question unhelpful thoughts/beliefs.
        - Encourage noticing emotions as temporary passing states.
        - Focus on changing behaviors and routines.
        - Differentiate between Controllables and Uncontrollables.
        - Suggest tackling avoided tasks with simple to-do lists.
        YOU ARE NOT A THERAPIST.

        CONTEXT:
        User Input: \"$userMessage\"
        Detected State: $primaryLabel (" . round($primaryScore * 100) . "%)
        
        STRATEGY FOR THIS RESPONSE:
        $specificInstruction
        
        RESPONSE GUIDELINE:
        - You cannot have a conversation with the user. You will not see the follow up to your response.
        - Output ONLY the conversational response to the user.
        - DO NOT explain your reasoning.
        - DO NOT mention the strategy used.
        - DO NOT end by asking non-rhetorical question
        - Offer advice to what the user said
        - Start your response directly.";
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
        return redirect()->route('chat1.index')->with([
            'user_message' => $userMsg,
            'bot_response' => $botMsg
        ]);
    }
}
