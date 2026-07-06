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

console.log('reverb working..');

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
