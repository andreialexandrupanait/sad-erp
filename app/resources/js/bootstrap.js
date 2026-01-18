import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to easily build robust real-time web applications.
 */

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

// Initialize Echo - support both Reverb and Pusher
if (import.meta.env.VITE_REVERB_APP_KEY) {
    // Laravel Reverb configuration
    // WebSocket is routed through nginx on standard HTTPS port (443) for SSL termination
    window.Echo = new Echo({
        broadcaster: "reverb",
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: 80,
        wssPort: 443,
        forceTLS: true,
        enabledTransports: ["ws", "wss"],
    });
} else if (import.meta.env.VITE_PUSHER_APP_KEY) {
    // Pusher configuration (fallback)
    window.Echo = new Echo({
        broadcaster: "pusher",
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? "mt1",
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? undefined,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
        enabledTransports: ["ws", "wss"],
    });
}
