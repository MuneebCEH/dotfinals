<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAttendance;
use Illuminate\Support\Carbon;

class AttendanceBeaconController extends Controller
{
    public function heartbeat(Request $request)
    {
        $today = Carbon::now('Asia/Karachi')->startOfDay();

        $attendance = UserAttendance::where('user_id', Auth::id())
            ->whereDate('check_in', $today)
            ->whereNull('check_out')
            ->first();

        if ($attendance) {
            $attendance->update([
                'last_heartbeat_at' => Carbon::now(),
                'status' => 'in',
            ]);
        }
        return response()->noContent();
    }

    public function close(Request $request)
    {
        $today = Carbon::now('Asia/Karachi')->startOfDay();

        $attendance = UserAttendance::where('user_id', Auth::id())
            ->whereDate('check_in', $today)
            ->whereNull('check_out')
            ->first();

        if ($attendance) {
            // Do NOT hard checkout here (multi-tab risk). Just stamp last heartbeat.
            $attendance->update(['last_heartbeat_at' => Carbon::now()]);
        }
        return response()->noContent();
    }
}
