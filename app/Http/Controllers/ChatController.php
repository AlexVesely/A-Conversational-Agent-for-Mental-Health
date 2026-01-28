<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        // Retrieve previous response from session if it exists
        $response = session('response', null);
        return view('chat', ['response' => $response]);
    }

    public function handle(Request $request)
    {
        $userMessage = $request->input('message');

        $response = "Hello, I see you said: $userMessage"; // TODO: Replace this with filtering + NLP + ruleset + LLM pipeline

        // Store response in session to show after redirect
        session(['response' => $response]);

        // Redirect to GET route to prevent POST resubmission
        return redirect()->route('chat.index');
    }
}

