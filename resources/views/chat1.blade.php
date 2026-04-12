<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mental Health Chatbot</title>
    <link rel="icon" href="{{ asset('SmilingEmoji.png') }}">
    <style>
        body { 
            font-family: sans-serif;
            display: flex;
            flex-direction: column; 
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f4f4f9;
        }

        .container { 
            text-align: center; 
            width: 80%; 
            max-width: 600px; 
            background: white; 
            padding: 2rem; 
            border-radius: 8px;
        }

        textarea { 
            width: 100%; 
            border: 1px solid #cccccc;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            font-size: 1rem;
            box-sizing: border-box;
            resize: none;
        }

        button { 
            background-color: #268b1bc4;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer; 
            margin-top: 10px; 
            width: 100%; 
        }

        button:hover { opacity: 0.9; }

        .response-box { 
            margin-top: 20px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 4px;
            text-align: left; 
        }

        .error-box { 
            color: red;
            background: #f8d7da;
            padding: 2px;
            border-radius: 2px;
            margin-bottom: 2px; 
        }

        .transparency-notice {
        background-color: #eef2f7;
        border-left: 4px solid #268b1bc4;
        padding: 15px;
        margin-top: 15px;
        margin-bottom: 5px;
        text-align: left;
        border-radius: 4px;
        }

        .transparency-notice p {
            margin: 0 0 8px 0;
            font-size: 0.9rem;
            color: #333;
        }

        .transparency-notice ul {
            margin: 0;
            padding-left: 20px;
            font-size: 0.85rem;
            color: #555;
            list-style-type: disc;
        }

        .transparency-notice li {
            margin-bottom: 5px;
        }
    
       
        .crisis-container { border: 3px solid #dc3545; background-color: #fff5f5; padding: 20px; border-radius: 8px; }
        .crisis-header { color: #dc3545; font-weight: bold; font-size: 1.2rem; display: block; margin-bottom: 10px; }

        .emotion-banner { font-weight: bold; text-transform: uppercase; font-size: 1.5rem; margin-bottom: 8px; display: block; }

        /* Specific Emotion Colors */
        .text-anger { color: #ff322b; } /* Red */
        .text-sadness { color: #3fbbe4; } /* Light Blue */
        .text-disgust { color: #16950d; } /* Dark Green */
        .text-fear { color: #be40be; } /* Purple */
        .text-surprise { color: #ff6200; } /* Orange */
        .text-joy { color: #ffc107; }     /* Gold */
        .text-neutral { color: #6c757d; } /* Grey */
    </style>
</head>
<body>
    <div class="container">
        <h1>Mental Health Chatbot</h1>
        @if ($errors->any())
            <div class="error-box">
                <strong>Error:</strong>
                <ul> 
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('chat1.handle') }}">
            @csrf
            <p>Tell the chatbot how you are feeling today: </p>
            <textarea id="message" name="message" rows="6" placeholder="Type here...">{{ old('message') }}</textarea>

            <div class="transparency-notice">
                <p><strong>Transparency Notice:</strong></p>
                <ul>
                    <li><strong>Support Tool Only:</strong> This chatbot is not a substitute for a professional therapist or clinical medical advice.</li>
                    <li><strong>Local Data Policy:</strong> This application is stateless. No conversation history is being stored by this software.</li>
                    <li>
                        <strong>Third-Party Processing:</strong> Inputs are transmitted to Hugging Face for real-time analysis. Per their 
                        <a href="https://huggingface.co/privacy" style="color: #268b1bc4; text-decoration: underline;">Privacy Policy</a>: 
                        information may be used for "business operations or scientific purposes" and "analysis or research."
                    </li>            
                    <li><strong>Safety Protocol:</strong> High-risk inputs will trigger a crisis detection system and output critical support information.</li>        
                    <li><strong>Purpose:</strong> This tool is specifically programmed to provide mental health support based on Cognitive Behavioral Therapy (CBT) principles.</li>
                </ul>
            </div>
            
            <button type="submit">Submit Message</button>
        </form>

        <!-- Check if a response exists in the session, if yes render the box with the response -->
        @if(session('bot_response'))
            <div class="response-box {{ session('is_crisis') ? 'crisis-container' : '' }}">
                @if(session('is_crisis'))
                    <span class="crisis-header">⚠️ CRISIS PROTOCOL ACTIVATED</span>
                @else
                    <span class="emotion-banner text-{{ session('primary_label') }}">
                        Emotion Detection: {{ ucfirst(session('primary_label')) }}
                    </span>
                @endif
                
                <p><strong>You said:</strong> {{ session('user_message') }}</p>
                <hr>
                <p><strong>Assistant Response:</strong></p>
                <!-- Convert Markdown response to HTML and render it -->
                <div class="bot-bubble">
                    {!! Str::markdown(session('bot_response')) !!}
                </div>
            </div>
        @endif
    </div>
</body>
</html>