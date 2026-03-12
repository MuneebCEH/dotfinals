<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin');

        if ($isAdmin) {
            $announcements = Announcement::with(['sender', 'recipient'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            $users = User::where('id', '!=', Auth::id())->get();
            return view('announcements.index', compact('announcements', 'users', 'isAdmin'));
        } else {
            // User sees announcements where recipient_id is null (All) or matches their ID
            $announcements = Announcement::with('sender')
                ->where(function ($query) use ($user) {
                    $query->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return view('announcements.index', compact('announcements', 'isAdmin'));
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'recipient_id' => 'nullable|exists:users,id',
        ]);

        Announcement::create([
            'user_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'is_active' => true,
        ]);

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function toggleStatus(Announcement $announcement)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $announcement->update(['is_active' => !$announcement->is_active]);

        return back()->with('success', 'Announcement status updated.');
    }

    public function destroy(Announcement $announcement)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
