<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Note::where('user_id', auth()->id())
            ->latest()
            ->get();

        // Optional: if you still pass $users to the filters UI
        $users = []; // or User::select('id','name')->get();

        // support inline edit toggle with ?edit={id}
        $editingId = $request->query('edit');

        return view('notes.index', compact('notes', 'users', 'editingId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Note::create([
            'name' => $validated['name'],
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            // ensure your migration has: ->nullable() for lead_id
            'lead_id' => null,
        ]);

        return redirect()->route('notes.index')->with('success', 'Note added.');
    }

    public function edit(Note $note)
    {
        $this->authorizeNote($note);
        // If you prefer a dedicated edit page, return a view here.
        // We use inline editing on index, so just redirect with a query param.
        return redirect()->route('notes.index', ['edit' => $note->id]);
    }

    public function update(Request $request, Note $note)
    {
        $this->authorizeNote($note);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $note->update($validated);

        return redirect()->route('notes.index')->with('success', 'Note updated.');
    }

    public function destroy(Note $note)
    {
        $this->authorizeNote($note);
        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted.');
    }

    private function authorizeNote(Note $note)
    {
        abort_unless($note->user_id === auth()->id(), 403);
    }
}
