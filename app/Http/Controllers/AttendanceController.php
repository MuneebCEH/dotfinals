<?php

namespace App\Http\Controllers;

use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Returns [startUtc, endUtc] for "today" using Asia/Karachi local day.
     */
    private function todayBoundsUtc(): array
    {
        $tz = 'Asia/Karachi';
        $startLocal = now($tz)->startOfDay();
        $endLocal   = now($tz)->endOfDay();

        // Convert to UTC for DB comparisons
        $startUtc = $startLocal->copy()->setTimezone('UTC');
        $endUtc   = $endLocal->copy()->setTimezone('UTC');

        return [$startUtc, $endUtc];
    }

    /**
     * Return the user's latest attendance row for the local "today" window.
     */
    private function latestTodayAttendance(int $userId): ?UserAttendance
    {
        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        return UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->latest('check_in')
            ->first();
    }

    public function checkIn(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'You must be logged in.');
        }

        $existing = $this->latestTodayAttendance($userId);

        // Already checked in (open session for today)
        if ($existing && $existing->check_out === null) {
            return back()->with('error', 'You are already checked in.');
        }

        // If there is a previous record for today but it's checked out,
        // create a NEW record to preserve history (recommended).
        $attendance = new UserAttendance();
        $attendance->user_id = $userId;
        $attendance->check_in = now(); // stored in app timezone/UTC per your config

        if ($request->filled('notes')) {
            $attendance->notes = trim($request->string('notes'));
        }

        $attendance->save();

        return back()->with('success', 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'You must be logged in.');
        }

        // Find the latest OPEN session in today's local window
        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        $attendance = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No active session to check out from.');
        }

        $attendance->check_out = now();
        $attendance->hours_worked = $this->diffInHours($attendance->check_in, $attendance->check_out);

        if ($request->filled('notes')) {
            $attendance->notes = trim(rtrim($attendance->notes . "\n" . $request->string('notes')));
        }

        $attendance->save();

        return back()->with('success', 'Checked out successfully.');
    }

    /**
     * Called from Logout/session-timeout to close the open attendance entry (if any).
     * Silent/no redirect; safe to call anywhere.
     */
    public function forceSessionCheckout(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        $attendance = UserAttendance::where('user_id', $user->id)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if ($attendance) {
            $now = now();
            $attendance->check_out = $now;
            $attendance->hours_worked = $this->diffInHours($attendance->check_in, $now);
            $attendance->notes = trim(rtrim(($attendance->notes ?? '') . "\n[Auto-checkout on session end]"));
            $attendance->save();
        }
    }

    private function diffInHours($start, $end): float
    {
        // Keep full precision; format in the view if you want 2 decimals
        return Carbon::parse($start)->diffInMinutes(Carbon::parse($end)) / 60;
    }

    public function history()
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'You must be logged in.');
        }

        $attendances = UserAttendance::where('user_id', $userId)
            ->orderBy('check_in', 'desc')
            ->paginate(10);

        return view('attendance.history', compact('attendances'));
    }

    public function getTodayAttendance()
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }

        return $this->latestTodayAttendance($userId);
    }

    public static function sessionAutoCheckout(int $userId): void
    {
        // Using the instance method helpers would be cleaner, but keep static-compatible logic:
        $tz = 'Asia/Karachi';
        $startUtc = now($tz)->startOfDay()->setTimezone('UTC');
        $endUtc   = now($tz)->endOfDay()->setTimezone('UTC');

        $now = now();

        $attendance = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if ($attendance) {
            $attendance->check_out = $now;
            $attendance->hours_worked = Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60;
            $attendance->notes = trim(rtrim(($attendance->notes ?? '') . "\n[Auto-checkout]"));
            $attendance->save();
        }
    }
}
