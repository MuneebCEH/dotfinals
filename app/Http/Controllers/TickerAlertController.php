<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TickerAlertController extends Controller
{
    public function index()
    {
        $alerts = \App\Models\TickerAlert::orderBy('created_at', 'desc')->paginate(10);
        return view('ticker-alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'theme_color' => 'required|string|in:red,green,blue,yellow,purple,slate',
            'disable_others' => 'nullable|boolean',
        ]);

        if ($request->has('disable_others')) {
            \App\Models\TickerAlert::query()->update(['is_active' => false]);
        }

        \App\Models\TickerAlert::create([
            'message' => $request->message,
            'theme_color' => $request->theme_color,
            'is_active' => true,
        ]);

        return back()->with('success', 'Live ticker alert updated successfully.');
    }

    public function toggleStatus(\App\Models\TickerAlert $ticker)
    {
        $ticker->update(['is_active' => !$ticker->is_active]);
        return back()->with('success', 'Alert status updated.');
    }

    public function destroy(\App\Models\TickerAlert $ticker)
    {
        $ticker->delete();
        return back()->with('success', 'Alert deleted successfully.');
    }
}
