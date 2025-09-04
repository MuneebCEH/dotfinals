Realtime Notifications Minimal Patch

1) Copy files from this archive into your project root, preserving paths:
   - routes/channels.php
   - app/Events/IssueCreated.php
   - app/Notifications/IssueCreatedNotification.php
   - resources/js/bootstrap.js
   - resources/views/layouts/partials/notifications_echo.blade.php

2) Include the Blade partial in your base layout (once), preferably before </body>:
   @include('layouts.partials.notifications_echo')

3) Ensure broadcasting is registered in bootstrap/app.php (Laravel 12):
   ->withBroadcasting(__DIR__ . '/../routes/channels.php')

4) .env (example dev values):
   BROADCAST_CONNECTION=reverb
   REVERB_APP_KEY=local
   REVERB_HOST=localhost
   REVERB_PORT=8080
   REVERB_SCHEME=http
   VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
   VITE_REVERB_HOST="${REVERB_HOST}"
   VITE_REVERB_PORT="${REVERB_PORT}"
   VITE_REVERB_SCHEME="${REVERB_SCHEME}"

5) Rebuild & run:
   npm run dev
   php artisan optimize:clear
   php artisan reverb:start

Notes:
- IssueCreated event broadcasts immediately (ShouldBroadcastNow), no queue worker required.
- Notifications now use the private channel App.Models.User.{id} and include 'broadcast' in via().
