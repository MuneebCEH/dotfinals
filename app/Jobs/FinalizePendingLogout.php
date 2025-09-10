<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FinalizePendingLogout implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $sessionId, public int $userId) {}

    public function handle(): void
    {
        $key = "pending_logout:{$this->sessionId}";

        // If still pending, kill the session (DB session driver) and invalidate remember tokens
        if (Cache::has($key)) {
            // Clear pending flag
            Cache::forget($key);

            // If using database sessions, delete this session row
            try {
                DB::table(config('session.table', 'sessions'))->where('id', $this->sessionId)->delete();
            } catch (\Throwable $e) {
                // silently ignore if not using DB sessions
            }

            // Invalidate remember token for safety (optional)
            try {
                $userModel = config('auth.providers.users.model');
                if (class_exists($userModel)) {
                    $user = $userModel::find($this->userId);
                    if ($user) {
                        $user->setRememberToken(\Str::random(60));
                        $user->save();
                    }
                }
            } catch (\Throwable $e) {}

            // Nothing else to return; session is gone. Next request will be unauthenticated.
        }
    }
}
