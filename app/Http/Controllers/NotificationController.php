<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Mark all database notifications as read
        $user->unreadNotifications()->update(['read_at' => now()]);

        // Mark all notifications as read by updating the user's timestamp
        $user->markAllNotificationsAsRead();

        return response()->json(['success' => true]);
    }

    public function markOneAsRead(Request $request, DatabaseNotification $notification)
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Ensure the notification belongs to the current user
        abort_unless($notification->notifiable_id === $user->getKey(), 403);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
