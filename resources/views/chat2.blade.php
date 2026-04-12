<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mental Health Chatbot</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background-color: #f4f4f9; }
        .container { text-align: center; width: 80%; max-width: 600px; background: white; padding: 2rem; border-radius: 8px; }
        textarea { width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px; margin-top: 10px; font-size: 1rem; }
        button { background-color: #007bff; color: white;  padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px; width: 100%; }
        .response-box { margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 4px; text-align: left; }
        .error-box { color: red; background: #f8d7da; padding: 2px; border-radius: 2px; margin-bottom: 2px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mental Health Chatbot</h1>
        @if ($errors->any())
        <div class="error-box">
            <strong>Error:</strong>
                <ul> @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <form method="POST" action="{{ route('chat2.handle') }}">
            @csrf
            <p>Hi, please tell me how you are feeling today:</p>
            <textarea name="message" rows="4" placeholder="Type your thoughts here...">{{ old('message') }}</textarea>
            <button type="submit">Submit Message</button>
        </form>

        @if(session('bot_response'))
            <div class="response-box">
                <p><strong>You said:</strong> {{ session('user_message') }}</p>
                <hr>
                <p><strong>Assistant Response:</strong></p>
                <!-- AI response is converted to plain text for security and transforms its nls into <br> -->
                <div class="bot-bubble">
                    {!! Str::markdown(session('bot_response')) !!}
                </div>
            </div>
        @endif
    </div>
</body>
</html>