<?php

namespace App\Http\Controllers;

use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Returns [startUtc, endUtc] for "today" using Asia/Karachi local day.
     */
    private function todayBoundsUtc(): array
    {
        $tz = 'Asia/Karachi';
        $startUtc = now($tz)->startOfDay()->setTimezone('UTC');
        $endUtc   = now($tz)->endOfDay()->setTimezone('UTC');
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
        if (!$userId) return $this->unauthResponse($request);

        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        // Open session is defined by status='in'
        $existing = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->where('status', 'in')
            ->latest('check_in')
            ->first();

        if ($existing) {
            // already checked in — just heartbeat; do NOT change check_in
            $existing->update(['last_heartbeat_at' => now()]);
            return $this->noContentIfJson($request, 'Already checked in.');
        }

        $now = now();

        // First check-in today
        UserAttendance::create([
            'user_id'           => $userId,
            'check_in'          => $now,       // set once
            'check_out'         => $now,       // start rolling window at login time
            'hours_worked'      => 0.00,       // start at zero
            'status'            => 'in',
            'last_heartbeat_at' => $now,
            'notes'             => $request->filled('notes') ? trim((string)$request->string('notes')) : null,
        ]);

        return $this->noContentIfJson($request, 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return $this->unauthResponse($request);

        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        $attendance = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->where('status', 'in')  // <-- open by status
            ->latest('check_in')
            ->first();

        if (!$attendance) {
            return $this->noContentIfJson($request, 'No active session.');
        }

        $now   = now();
        $hours = round(Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60, 2);

        $attendance->update([
            'check_out'         => $now,      // (already being rolled by ping; set anyway)
            'hours_worked'      => $hours,
            'last_heartbeat_at' => $now,
            'status'            => 'out',     // <-- mark closed
            'notes'             => $request->filled('notes')
                ? $this->appendNote($attendance->notes, trim((string)$request->string('notes')))
                : $attendance->notes,
        ]);

        return $this->noContentIfJson($request, 'Checked out successfully.');
    }

    /**
     * Optional: heartbeat endpoint to keep last_heartbeat_at fresh without changing in/out state.
     */
    public function ping(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return response()->noContent();

        // Find today's *open* attendance using status='in'
        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        $attendance = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->where('status', 'in')         // <-- use status, not check_out NULL
            ->latest('check_in')
            ->first();

        if ($attendance) {
            $now   = now();
            $hours = round(Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60, 2);

            $attendance->update([
                'check_out'         => $now,  // <-- roll the end time forward
                'hours_worked'      => $hours,
                'last_heartbeat_at' => $now,  // (optional but useful)
            ]);
        }

        return response()->noContent();
    }


    /**
     * Called from Logout/session-timeout to close the open attendance entry (if any).
     * Silent/no redirect; safe to call anywhere.
     */
    public function forceSessionCheckout(): void
    {
        $user = Auth::user();
        if (!$user) return;

        [$startUtc, $endUtc] = $this->todayBoundsUtc();

        $attendance = UserAttendance::where('user_id', $user->id)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->where('status', 'in')   // <-- open by status
            ->latest('check_in')
            ->first();

        if ($attendance) {
            $now   = now();
            $hours = round(Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60, 2);

            $attendance->update([
                'check_out'         => $now,   // finalize to now
                'hours_worked'      => $hours,
                'last_heartbeat_at' => $now,
                'status'            => 'out',
                'notes'             => $this->appendNote($attendance->notes, '[Auto-checkout on session end]'),
            ]);
        }
    }

    private function diffInHours($start, $end): float
    {
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
        $tz = 'Asia/Karachi';
        $startUtc = now($tz)->startOfDay()->setTimezone('UTC');
        $endUtc   = now($tz)->endOfDay()->setTimezone('UTC');
        $now = now();

        $attendance = UserAttendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc])
            ->where('status', 'in')                    // <-- changed
            ->latest('check_in')
            ->first();

        if ($attendance) {
            $attendance->update([
                'check_out'         => $now,
                'hours_worked'      => round(Carbon::parse($attendance->check_in)->diffInMinutes($now) / 60, 2),
                'last_heartbeat_at' => $now,
                'status'            => 'out',
                'notes'             => self::appendNoteStatic($attendance->notes, '[Auto-checkout]'),
            ]);
        }
    }

    // ---------- small helpers ----------

    private function appendNote(?string $existing, string $add): string
    {
        $existing = trim((string)$existing);
        $add = trim($add);
        return $existing === '' ? $add : ($existing . PHP_EOL . $add);
    }

    private static function appendNoteStatic(?string $existing, string $add): string
    {
        $existing = trim((string) $existing);
        $add = trim($add);
        if ($existing === '') return $add;
        return $existing . PHP_EOL . $add;
    }

    private function unauthResponse(Request $request)
    {
        if ($request->expectsJson() || str_contains($request->header('Accept', ''), 'json')) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        return back()->with('error', 'You must be logged in.');
    }

    private function noContentIfJson(Request $request, string $flash)
    {
        if ($request->expectsJson() || str_contains($request->header('Accept', ''), 'json')) {
            return response()->noContent();
        }
        return back()->with('success', $flash);
    }
}
