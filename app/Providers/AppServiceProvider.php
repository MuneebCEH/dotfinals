<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;
use App\Models\UserAttendance;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- Your existing timezone/locale setup ---
        config(['app.timezone' => 'Asia/Karachi']);
        date_default_timezone_set('Asia/Karachi');
        Carbon::setLocale('en');

        // --- Auto-checkout on logout (no EventServiceProvider needed) ---
        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            if (!$user) {
                return;
            }

            $now = Carbon::now();
            $attendance = UserAttendance::where('user_id', $user->id)
                ->whereDate('check_in', $now->toDateString())
                ->whereNull('check_out')
                ->first();

            if ($attendance) {
                $attendance->check_out = $now;
                $attendance->hours_worked = Carbon::parse($attendance->check_in)
                    ->diffInMinutes($now) / 60;
                $attendance->notes = trim(($attendance->notes ?? '') . "\n[Auto-checkout on logout]");
                $attendance->save();
            }
        });
    }
}
