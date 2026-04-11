<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mental Health Chatbot</title>
</head>

<body>

    <h1>Mental Health Chatbot</h1>

    @if ($errors->any())
        <div class="error-box">
            <strong>Error:</strong>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form method="POST" action="{{ route('chat.handle') }}">
        @csrf
        <p>Hi, please tell me how you are feeling today: </p>
        <textarea 
            id="message"
            name="message" 
            rows="5"
            cols="50"
            placeholder="Type your thoughts here..."
        >{{ old('message') }}</textarea>
        
        <button type="submit">Send</button>
    </form>

    @if(session('bot_response'))
        <div class="response-box">
            
            <p><strong>You said:</strong></p>
            <p> {{ session('user_message') }}</p>

            <p><strong>Assistant Response:</strong></p>
            <!-- AI response is converted to plain text for security and transforms its nls into <br> -->
            <div>{!! nl2br(e(session('bot_response'))) !!}</div>

        </div>
    @endif

</body>
</html>