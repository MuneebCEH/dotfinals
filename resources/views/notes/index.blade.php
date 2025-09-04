@extends('layouts.app')

@section('title', 'Notes')
@section('page-title', 'Notes')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-8" x-data="{
        showForm: false,
        showEditModal: false,
        editingNote: null,
        editForm: {
            id: null,
            name: '',
            body: ''
        },
        openEditModal(note) {
            this.editingNote = note;
            this.editForm.id = note.id;
            this.editForm.name = note.name;
            this.editForm.body = note.body;
            this.showEditModal = true;
        },
        closeEditModal() {
            this.showEditModal = false;
            this.editingNote = null;
            this.editForm = { id: null, name: '', body: '' };
        }
    }">
        <!-- Header -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Notes Feed</h2>
                <p class="text-gray-600 dark:text-gray-400">Your personal reminders and notes.</p>
            </div>
            <!-- Create Note Button -->
            <button @click="showForm = !showForm"
                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                <span x-show="!showForm">+ Create Note</span>
                <span x-show="showForm" x-cloak>× Close</span>
            </button>
        </div>

        <!-- Success/Error Messages -->
        {{-- @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        <!-- Create Note Form (hidden until clicked) -->
        <div x-show="showForm" x-transition x-cloak
            class="bg-white/90 dark:bg-gray-800/80 rounded-2xl shadow-lg border border-gray-200/70 dark:border-gray-700/60 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200/70 dark:border-gray-700/60">
                <h3 class="text-lg font-semibold">Add a Personal Note</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Store quick reminders just for you.</p>
            </div>

            <form action="{{ route('notes.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Note Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium mb-1">Note Body</label>
                    <textarea id="body" name="body" rows="4"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 resize-y"
                        required>{{ old('body') }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="showForm = false"
                        class="px-4 py-2 rounded-xl border hover:bg-gray-50 dark:hover:bg-gray-700/50">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Add
                        Note</button>
                </div>
            </form>
        </div>

        <!-- Notes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($notes as $note)
                <div
                    class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="font-semibold">{{ $note->name }}</h4>
                        <span class="block text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                        <p class="mt-3 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $note->body }}</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button @click="openEditModal({{ $note->toJson() }})"
                            class="px-3 py-1.5 text-sm rounded-lg border hover:bg-gray-50 dark:hover:bg-gray-700/50">Edit</button>
                        <form action="{{ route('notes.destroy', $note) }}" method="POST"
                            onsubmit="return confirm('Delete this note?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 text-center text-gray-500 border border-dashed rounded-xl p-8">
                    No notes yet — create your first one above.
                </div>
            @endforelse
        </div>

        <!-- Edit Note Modal -->
        <div x-show="showEditModal" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeEditModal()">

            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeEditModal()"></div>

            <!-- Modal Content -->
            <div
                class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <!-- Modal Header -->
                <div
                    class="px-6 py-5 border-b border-gray-200/70 dark:border-gray-700/60 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Edit Note</h3>
                        <button type="button" @click="closeEditModal()" class="p-1 rounded hover:bg-white/20 transition"
                            aria-label="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <form :action="`{{ route('notes.index') }}/${editForm.id}`" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_name" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Note
                            Name</label>
                        <input type="text" id="edit_name" name="name" x-model="editForm.name"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                    </div>

                    <div>
                        <label for="edit_body" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Note
                            Body</label>
                        <textarea id="edit_body" name="body" rows="6" x-model="editForm.body"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"
                            required></textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/70 dark:border-gray-700/60">
                        <button type="button" @click="closeEditModal()"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                            Update Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
