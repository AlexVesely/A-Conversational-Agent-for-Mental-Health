<!DOCTYPE html>
<html>
<head>
    <title>Mental Health Chatbot</title>
</head>
<body>
    <h1>Mental Health Chatbot</h1>

    <form method="POST" action="{{ route('chat.handle') }}">
        @csrf
        <textarea name="message" rows="5" cols="50">{{ old('message') }}</textarea>
        <button type="submit">Send</button>
    </form>

    @isset($response) <!-- If $response exists. It wont the first time the page is open -->
        <h3>Response</h3>
        <p>{{ $response ?? 'No response yet' }}</p>
    @endisset
    
</body>
</html>
