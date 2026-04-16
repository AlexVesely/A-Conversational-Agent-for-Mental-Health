<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EnhancedChatController extends Controller
{
    public function index()
    {
        return view('chatA', ['response' => session('response')]);
    }

    public function handle(Request $request)
    {        // Validation: Ensure message is valid
        $request->validate(
            ['message' => 'required|string|max:1000|min:2'], // checking message exists, is a string and less than 1000 chars
            [
                'message.required' => 'Please enter a message.',
                'message.max'      => 'Your message is too long. Please keep it under 1000 characters.',
                'message.min'      => 'Your message is too short. Please enter more than 1 character.'
            ]        
        );

        // IF the validation passes, move on to the code underneath

        $userMessage = $request->input('message'); // Retreives input from 'message form'

        // Safety Filter
        $flaggedWord = $this->runSafetyFilter($userMessage);

        // if flaggedWord is not null
        if ($flaggedWord) {
        $crisisResponse = "**CRITICAL ALERT:** The input entered has triggered the crisis detection protocol. This chatbot is not equipped to handle your input.\n\n" .
                        "Sharing current feelings with a **trusted person** or a **professional** is strongly encouraged. Support is available.\n\n" .
                        "### Immediate Help Resources\n\n" . 
                        "* **NHS:** Call **111** for urgent mental health advice.\n" .
                        "* **SHOUT:** Text **SHOUT** to **85258** for free support.\n" . 
                        "* **Emergency:** Call **999** in immediate danger.";

        return $this->redirectWithResponse($userMessage, $crisisResponse, true);
    }

        // Emotion Classification
        $apiData = $this->getEmotionData($userMessage);

        $primaryLabel = $apiData['api_response'][0][0]['label']; // Extract primary emotion from input

        // Build Prompt
        $llmPrompt = $this->buildLlmPrompt($userMessage,$apiData);

        // Get LLM response
        $botReply = $this->callLlm($llmPrompt);
        
        // Formatting the Output into String (DEBUGGING CODE)
        // $jsonString = json_encode($apiData);
        // $debuggingResponse = "Hello, I see you said: \"$userMessage\". " . 
        //          "Detected emotion(s) data: $jsonString" . 
        //          "DECISION TREE OUTPUT: $llmPrompt";

        // Send back what the user said and the bot reply
        return $this->redirectWithResponse($userMessage, $botReply, false, $primaryLabel);
    }

    //Returns the flagged crisis word if found, otherwise null.
    //?string in type signature indicates either string or null can be returned
    private function runSafetyFilter(string $text): ?string
    {
        $userMessageLower = strtolower($text); // Transform text to lower case
        $normalisedText = preg_replace('/[[:punct:]\s]+/', '', $userMessageLower); // Remove all punctuation and spaces

        // Direct Keyword Check
        $crisisKeywords = ['killmyself','suicide','suicidal','selfharm','enditall','endingitall','noreasontolive',
                           'endmylife', 'endeverything', 'takemyownlife','cutmyself', 'cuttingmyself',
                           'injuremyself','injuringmyself','nothingtolivefor','lifeispointless','tiredofliving',
                           'dontwanttoexist','wishiwasnthere','wishicoulddisappear','wanttodisappear','ratherbedead',
                           'icanttakethisanymore','kys','kms','unalivemyself','deletemyself'];
        foreach ($crisisKeywords as $keyword) {
            if (str_contains($normalisedText, $keyword)) {
                return $keyword; 
            }
        }

        // 3. PATTERN MATCHING (On original text for misspellings)
        $patterns = [
            '/\bsuicid[aeiouy]*l*\b/i', 
            '/\bkill\s*my\s*self\b/i', 
            '/\bself[\s\-]*harm\b/i', 
            '/\bend\s*it\s*all?\b/i', 
            '/\bwanna\s*die|want\s*to\s*die\b/i',
            '/\bbetter\s*of+\s*dead\b/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[0]; 
            }
        }

        return null; // No crisis detected
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

        $primaryLabel = $primaryEmotion['label'];

        // Strategy for the specificInstruction is adapted from https://www.mindmypeelings.com/blog/cbt-principles
        $specificInstruction = "";

        // Map emotions to an appropriate CBT support technique
        $specificInstruction = match ($primaryLabel) {
        'anger'    => "STRICT REQUIREMENT: Use the 'ABC Model.' Break down the response into: Activating Event, Belief, and Consequence.",          
        'sadness'  => "STRICT REQUIREMENT: Utilise 'Activity Scheduling.' Suggest identifying and scheduling a small, rewarding behavior or hobby to improve mood through action.",
        'disgust'  => "STRICT REQUIREMENT: Employ 'Cognitive Restructuring.' Assist in identifying and reframing the irrational thought.",
        'fear'     => "STRICT REQUIREMENT: Use the 'Worst Case/Best Case/Most Likely Case Scenario.' Explicitly list all three scenarios to rationalize the fear.",
        'surprise' => "STRICT REQUIREMENT: Utilise 'Guided Discovery.' Ask open-ended questions to help broaden thinking and process the event again.",
        'joy'      => "STRICT REQUIREMENT: Focus on 'Acceptance and Commitment Therapy.' Encourage acceptance and embracing the joy." ,
        'neutral'  => "STRICT REQUIREMENT: Encourage 'Journaling.' Help build awareness of potential cognitive errors and understading personal cognition",
        default    => "STRICT REQUIREMENT: Provide general supportive guidance based on the Cognitive Triangle (Thoughts impact Feelings which impact Behaviors)."
        };
        
        return "
        ROLE:
        -Supportive Mental Health Conversation Assistant. Use CBT principles.

        CRITICAL RULES:
        - NEVER use first-person pronouns: 'I', 'me', 'my', 'mine', 'myself'.
        - YOU ARE NOT A THERAPIST.
        - DO NOT RESPOND TO ANYTHING THAT IS NOT MENTAL HEALTH RELATED.
        - Ensure the response is standalone, the user cannot respond to you.

        PRIMARY INSTRUCTION
        - You must apply the following CBT technique to the user message. 
        - TECHNIQUE TO USE: $specificInstruction
        - USER INPUT: \"$userMessage\"
        ";
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
                "max_tokens" => 800, // Limits length of AI reply
                "stream" => false // Receive AI response all at once rather than word by word
            ]);

        if ($response->failed()) {
            // Writes error in storage/logs/laravel.log
            \Log::error('HF LLM Error: ' . $response->status() . ' - ' . $response->body());
            return "Trouble connecting to the LLM. Try again?";
        }

        $result = $response->json(); //Transform text into php array

        // Format is choices -> message -> content to find the AI response
        return $result['choices'][0]['message']['content'];
    }

    // Helper to handle the redirect and session storage
    private function redirectWithResponse(string $userMsg, string $botMsg, bool $isCrisis = false, string $emotion = 'neutral')
    {
        return redirect()->route('chatA.index')->with([
            'user_message' => $userMsg,
            'bot_response' => $botMsg,
            'is_crisis'    => $isCrisis, // Pass this to the view
            'primary_label' => $emotion
        ]);
    }
}
