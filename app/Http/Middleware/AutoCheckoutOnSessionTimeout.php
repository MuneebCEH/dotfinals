<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\UserAttendance;

class AutoCheckoutOnSessionTimeout
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $now = Carbon::now();
            $last = $request->session()->get('last_activity'); // unix ts
            $lifetimeMinutes = (int) config('session.lifetime', 120);

            if ($last) {
                $lastAt = Carbon::createFromTimestamp($last);
                if ($lastAt->diffInMinutes($now) >= $lifetimeMinutes) {
                    // Session considered expired → close attendance & logout
                    $attendance = UserAttendance::where('user_id', Auth::id())
                        ->whereDate('check_in', $now->toDateString())
                        ->whereNull('check_out')
                        ->first();

                    if ($attendance) {
                        $attendance->check_out = $now;
                        $attendance->hours_worked = $attendance->check_in
                            ? Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60
                            : 0;
                        $attendance->notes = trim(($attendance->notes ?? '') . "\n[Auto-checkout on session timeout]");
                        $attendance->save();
                    }

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('info', 'Session expired; you were auto-checked-out.');
                }
            }

            // refresh activity timestamp each request
            $request->session()->put('last_activity', $now->timestamp);
        }

        return $next($request);
    }
}
