console.log('💬 Chat module loaded');

// Chat functionality
window.chatRooms = new Map(); // Store active chat rooms

// Listen to chat messages for a specific room
window.listenToChatRoom = function(roomId) {
    if (window.chatRooms.has(roomId)) {
        return; // Already listening to this room
    }

    const channel = window.Echo.private(`chat-room.${roomId}`)
        .listen('.message.sent', (e) => {
            console.log('New message received:', e);
            addMessageToChat(e);
        });

    window.chatRooms.set(roomId, channel);
    console.log(`📨 Listening to chat room: ${roomId}`);
};

// Stop listening to a chat room
window.stopListeningToChatRoom = function(roomId) {
    if (window.chatRooms.has(roomId)) {
        window.Echo.leave(`chat-room.${roomId}`);
        window.chatRooms.delete(roomId);
        console.log(`🚫 Stopped listening to chat room: ${roomId}`);
    }
};

// Add message to chat UI
function addMessageToChat(messageData) {
    const chatContainer = document.getElementById('chat-messages');
    if (!chatContainer) {
        console.warn('Chat container not found');
        return;
    }

    const messageElement = document.createElement('div');
    messageElement.className = `message ${messageData.sender_type}`;
    messageElement.innerHTML = `
        <div class="message-header">
            <span class="message-sender">${messageData.sender.name}</span>
            <span class="message-time">${new Date(messageData.created_at).toLocaleTimeString()}</span>
        </div>
        <div class="message-content">${messageData.message}</div>
    `;
    
    chatContainer.appendChild(messageElement);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    // Play notification sound (optional)
    playNotificationSound();
}

// Play notification sound
function playNotificationSound() {
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.3;
    audio.play().catch(e => console.log('Could not play notification sound'));
}

// Clean up chat listeners when page unloads
window.addEventListener('beforeunload', function() {
    window.chatRooms.forEach((channel, roomId) => {
        window.Echo.leave(`chat-room.${roomId}`);
    });
});

// Send message function
window.sendChatMessage = function(roomId, message) {
    return fetch('/api/chat/send-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            room_id: roomId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Message sent successfully');
        }
        return data;
    })
    .catch(error => {
        console.error('Error sending message:', error);
    });
};