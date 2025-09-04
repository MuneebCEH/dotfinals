<?php

namespace App\Http\Controllers;

use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        // Check if user already checked in today
        $existingCheckIn = UserAttendance::where('user_id', Auth::id())
            ->whereDate('check_in', today())
            ->first();

        if ($existingCheckIn) {
            return redirect()->back()->with('error', 'You have already checked in today.');
        }

        UserAttendance::create([
            'user_id' => Auth::id(),
            'check_in' => now(),
            'notes' => $request->notes
        ]);

        return redirect()->back()->with('success', 'Successfully checked in at ' . now()->format('g:i A'));
    }

    public function checkOut(Request $request)
    {
        // Find today's check-in record
        $attendance = UserAttendance::where('user_id', Auth::id())
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'You need to check in first before checking out.');
        }

        $checkOutTime = now();
        $checkInTime = Carbon::parse($attendance->check_in);
        $hoursWorked = $checkInTime->diffInHours($checkOutTime);

        $attendance->update([
            'check_out' => $checkOutTime,
            'hours_worked' => $hoursWorked,
            'notes' => $attendance->notes ? $attendance->notes . ' | ' . $request->notes : $request->notes
        ]);

        return redirect()->back()->with('success', 'Successfully checked out at ' . $checkOutTime->format('g:i A') . '. Hours worked: ' . number_format($hoursWorked, 2));
    }

    public function history()
    {
        $attendances = UserAttendance::where('user_id', Auth::id())
            ->orderBy('check_in', 'desc')
            ->paginate(10);

        return view('attendance.history', compact('attendances'));
    }

    public function getTodayAttendance()
    {
        return UserAttendance::where('user_id', Auth::id())
            ->whereDate('check_in', today())
            ->first();
    }
}
