import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

console.log('⚡ Reverb initialized...');

if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Connected to Reverb WebSocket Server!');
    });

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('❌ Reverb Connection Error:', err);
    });
}

// Expose global helpers to subscribe/unsubscribe chat
window.joinChat = (conversationId, callback) => {
    console.log(`Subscribing to public channel: chat.${conversationId}`);
    return window.Echo.channel(`chat.${conversationId}`)
        .listen('.message.sent', (data) => callback('message.sent', data))
        .listen('.messages.read', (data) => callback('messages.read', data));
};

window.leaveChat = (conversationId) => {
    console.log(`Leaving channel: chat.${conversationId}`);
    window.Echo.leave(`chat.${conversationId}`);
};

// =======================================================
// TEST LISTENER: Current User ID 5 (Messaging User ID 6)
// Conversation ID: 1 (Channel: chat.1)
// =======================================================
const TEST_CONVERSATION_ID = 1; // Conversation between User 5 & User 6

console.log(`📡 [TEST] Subscribing to Reverb channel: chat.${TEST_CONVERSATION_ID}`);

window.Echo.channel(`chat.${TEST_CONVERSATION_ID}`)
    .listen('.message.sent', (data) => {
        console.log('📩 [TEST REVERB] New Message Received:', data);
    })
    .listen('.messages.read', (data) => {
        console.log('👁️ [TEST REVERB] Messages Marked as Read:', data);
    });
