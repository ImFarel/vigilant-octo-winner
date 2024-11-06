<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatroom</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.min.js"
        integrity="sha512-XYamWfde8fVJB0ruVwoA+rwH39JBVzBhQzQi22mV6aXMow3uCBWzN1ISCEkaJ2mZl2ktBZuteMoPKlMCGDwoPA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>
    <h1>Chatroom</h1>
    <div id="messages"></div>
    <script>
        // Configure Pusher
        Pusher.logToConsole = true;

        const echo = new Echo({
            broadcaster: 'reverb',
            key: 'kaxug0yvys5gvpzppfdl',
            wsHost: 'localhost',
            wsPort: 8080,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    Authorization: `3|jj5Mqr0uMp5ukcQG7iHhLoi94n9hFuPEr8aUhJJt067c8176`, // test2@mail.com
                },
            },
            authEndpoint: 'http://localhost:8080/broadcasting/auth',
        });

        // Listen for messages in the chatroom
        const chatroomId = 1; // Replace with the actual chatroom ID
        echo.private(`chatroom.${chatroomId}`)
            .listen('MessageSent', (e) => {
                const messagesDiv = document.getElementById('messages');
                const messageElement = document.createElement('div');
                messageElement.textContent = `${e.message.user.name}: ${e.message.message}`;
                messagesDiv.appendChild(messageElement);
            });
    </script>
</body>

</html>
