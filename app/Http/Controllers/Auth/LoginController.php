<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\UserAttendance;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // === CHECK-IN ===
            $this->checkInUser(Auth::id(), $request);

            // Redirect based on role (as you had)
            return match (Auth::user()->role) {
                'admin'          => redirect()->route('dashboard')
                    ->with('success', 'Welcome back, Admin!'),
                'report_manager' => redirect('/issues')
                    ->with('success', 'Welcome back, Report Manager!'),
                default          => redirect()->route('dashboard')
                    ->with('success', 'Welcome back!'),
            };
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput($request->only('email', 'remember'));
    }

    public function destroy(Request $request)
    {
        // === CHECK-OUT ===
        $this->checkOutUser(Auth::id());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Signed out.');
    }

    // Beacon logout (CSRF-exempt) – used on last-tab close
    public function logoutBeacon(Request $request)
    {
        if (Auth::check()) {
            // === CHECK-OUT ===
            $this->checkOutUser(Auth::id());

            // rotate remember token to prevent silent re-auth
            $user = Auth::user();
            $user->setRememberToken(Str::random(60));
            $user->save();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return response()->noContent(); // 204
    }

    /**
     * Lightweight heartbeat: mark user as active and bump last_heartbeat_at.
     * You can call this periodically from JS, or on visibility change.
     */
    public function pending(Request $request)
    {
        $userId = optional(Auth::user())->id;
        if (!$userId) {
            return response()->noContent();
        }

        try {
            $open = $this->getOpenAttendance($userId);
            if ($open) {
                $open->update([
                    'last_heartbeat_at' => now(),
                    // keep status explicitly "in" while active
                    'status'            => 'in',
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // Optional pending logout key (only needed if you also use a delayed job)
        $key = $this->pendingKey($request->session()->getId());
        Cache::put($key, now()->timestamp, now()->addSeconds(8));

        return response()->noContent();
    }

    public function cancel(Request $request)
    {
        $key = $this->pendingKey($request->session()->getId());
        Cache::forget($key);
        return response()->noContent();
    }

    private function pendingKey(?string $sessionId): string
    {
        return "pending_logout:" . ($sessionId ?? 'unknown');
    }

    // ------------------------- Helpers -------------------------

    /**
     * Create a single open attendance row if none exists.
     * If one already exists (status=in, no check_out), just bump heartbeat.
     */
    private function checkInUser(int $userId, Request $request): void
    {
        try {
            $open = $this->getOpenAttendance($userId);

            if ($open) {
                // already in: just refresh heartbeat
                $open->update([
                    'last_heartbeat_at' => now(),
                    'status'            => 'in',
                ]);
                return;
            }

            UserAttendance::create([
                'user_id'           => $userId,
                'check_in'          => now(),
                'status'            => 'in',
                'last_heartbeat_at' => now(),
                'notes'             => null,
                // ip/user-agent not in your schema; add columns if you want to store these
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Close the open attendance row and compute hours_worked.
     * If there’s no open row, it no-ops.
     */
    private function checkOutUser(?int $userId): void
    {
        if (!$userId) return;

        try {
            $open = $this->getOpenAttendance($userId);
            if (!$open) return;

            $checkIn  = Carbon::parse($open->check_in);
            $checkout = now();

            // base diff in minutes
            $minutes = max(0, $checkIn->diffInMinutes($checkout));
            $hours   = round($minutes / 60, 2);

            $open->update([
                'check_out'     => $checkout,
                'hours_worked'  => $hours,   // if you want cumulative per day, see note below
                'status'        => 'out',
                'last_heartbeat_at' => $checkout,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Fetch the single "open" row (status=in & no check_out).
     */
    private function getOpenAttendance(int $userId): ?UserAttendance
    {
        return UserAttendance::where('user_id', $userId)
            ->whereNull('check_out')
            ->where('status', 'in')
            ->latest('id')
            ->first();
    }
}
