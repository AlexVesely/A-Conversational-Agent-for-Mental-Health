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

    @if(session('response'))
        <div class="response-box">
            <h3>Assistant Response</h3>
            {{ session('response') }}
        </div>
    @endif

</body>
</html>