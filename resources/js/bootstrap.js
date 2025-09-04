// resources/js/bootstrap.js
import _ from "lodash";
window._ = _;

import Echo from "laravel-echo";

// If you use Alpine anywhere, keep these two lines (optional)
// import Alpine from "alpinejs";
// window.Alpine = Alpine; Alpine.start();

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY ?? "local",
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
    enabledTransports: ["ws", "wss"],
});
