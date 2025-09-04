<?php

namespace App\Http\Controllers;

use App\Models\Callback;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CallbackController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'today');
        $callbacks = Callback::where('user_id', Auth::id())
            ->latest()
            ->with(['lead', 'user'])
            ->paginate(20);

        // Leads owned by the user OR attached via lead_user pivot
        $leadsForUser = Lead::query()
            ->where('created_by', Auth::id())              // adjust if your owner column differs
            ->orWhereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->select('id', 'first_name', 'surname', 'numbers')
            ->orderBy('surname')                            // or first_name
            ->orderBy('first_name')
            ->get();

        return view('callbacks.index', compact('callbacks', 'leadsForUser', 'tab'));
    }

    public function create(Request $request)
    {
        // If you have a dedicated create page (not modal), keep this.
        $leadId = $request->query('lead_id');

        $leads = Lead::join('lead_user', 'leads.id', '=', 'lead_user.lead_id')
            ->where('lead_user.user_id', Auth::id())
            ->select('leads.id', 'leads.name')
            ->orderBy('leads.name')
            ->get();

        $lead = $leadId ? $leads->firstWhere('id', (int) $leadId) : null;

        return view('callbacks.create', compact('lead', 'leads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id'      => 'nullable|exists:leads,id',
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string',
        ]);

        Callback::create([
            'lead_id'      => $validated['lead_id'] ?? null,
            'user_id'      => Auth::id(),
            'scheduled_at' => $validated['scheduled_at'],
            'notes'        => $validated['notes'] ?? null,
            'status'       => 'pending',
        ]);

        return redirect()->route('callbacks.index')->with('success', 'Callback created successfully.');
    }

    public function edit(Callback $callback)
    {
        $this->authorize('update', $callback);

        $leads = Lead::join('lead_user', 'leads.id', '=', 'lead_user.lead_id')
            ->where('lead_user.user_id', Auth::id())
            ->select('leads.id', 'leads.name')
            ->orderBy('leads.name')
            ->get();

        return view('callbacks.edit', compact('callback', 'leads'));
    }

    public function update(Request $request, Callback $callback)
    {
        $this->authorize('update', $callback);

        $validated = $request->validate([
            'lead_id'      => 'nullable|exists:leads,id',
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:pending,completed,cancelled',
        ]);

        DB::transaction(function () use ($callback, $validated) {
            $callback->update($validated);

            // If the callback is completed and linked to a lead, mark the lead as submitted
            if (($validated['status'] ?? null) === 'completed' && $callback->lead_id) {
                dd($callback->lead());
                $callback->lead()->update(['status' => 'submitted']);
            }
        });

        return redirect()->route('callbacks.index')->with('success', 'Callback updated successfully.');
    }

    public function complete(Callback $callback)
    {
        $this->authorize('update', $callback);

        DB::transaction(function () use ($callback) {
            $callback->update(['status' => 'completed']);

            if ($callback->lead_id) {
                $callback->lead()->update(['status' => 'submitted']);
            }
        });

        return redirect()->route('callbacks.index')
            ->with('success', 'Callback marked as completed.');
    }

    public function reschedule(Request $request, Callback $callback)
    {
        $this->authorize('update', $callback);

        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $callback->update([
            'scheduled_at' => Carbon::parse($request->scheduled_at),
            'status'       => 'pending', // reset to pending if rescheduled
        ]);

        return redirect()->route('callbacks.index')
            ->with('success', 'Callback rescheduled successfully.');
    }

    public function destroy(Callback $callback)
    {
        $this->authorize('delete', $callback);

        $callback->delete();

        return redirect()->route('callbacks.index')
            ->with('success', 'Callback deleted successfully.');
    }
}
