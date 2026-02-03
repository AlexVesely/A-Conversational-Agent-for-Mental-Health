<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat', ['response' => session('response')]);
    }

    public function handle(Request $request)
    {
        // Validation: Ensure message is valid
        // !!!!!!! CURRENTLY MIN IS SET AS 1 !!!!!!!!!
        $request->validate(
            ['message' => 'required|string|max:1000|min:1'], // checking message exists, is a string and less than 1000 chars
            [
                'message.required' => 'Please enter a message.',
                'message.max'      => 'Your message is too long. Please keep it under 1000 characters.',
                'message.min'      => 'Your message is too short. Please enter more than 10 characters.'
            ]        
        );

        // IF the validation passes, move on to the code underneath

        $userMessage = $request->input('message'); // Retreuves input from 'message form'

        // Safety Filter
        $flaggedWord = $this->runSafetyFilter($userMessage);
        // if flaggedWord is not null
        if ($flaggedWord) {
            $alert = "ALERT: HIGH DISTRESS WORD DETECTED: \"$flaggedWord\". PLEASE SEEK A HUMAN THERAPIST.";
            return $this->redirectWithResponse($alert);
        }

        // Emotion Classification
        $apiData = $this->getEmotionData($userMessage);
        
        // Formatting the Output into String (Temporary for debugging)
        $jsonString = json_encode($apiData);
        $finalResponse = "Hello, I see you said: \"$userMessage\". Detected emotion(s) data: $jsonString";

        return $this->redirectWithResponse($finalResponse);
    }

    //Returns the flagged word if found, otherwise null.
    //?string in type signature indicates either string or null can be returned
    private function runSafetyFilter(string $text): ?string
    {
        $blacklistedWords = ['kill myself', 'suicide', 'self harm', 'end it all'];

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
            ->post($url, [ // Send data to the url
                'inputs' => $inputText,
                'options' => [
                    'use_cache' => false, // Must run calculation on input text
                    'wait_for_model' => true // Allow time for API response
                ]
            ]);

        return [ // return as an array
            'you_sent' => $inputText, //debugging to see what was sent to the model
            'api_response' => $response->json() //format JSON data into a php array
        ];
    }

    // Helper to handle the redirect and session storage.
    private function redirectWithResponse(string $message)
    {
        session(['response' => $message]); //saves last message/data in short-term memory
        return redirect()->route('chat.index');
    }
}
