<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600/75 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div
        class="relative top-20 mx-auto p-8 border w-96 shadow-2xl rounded-2xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border-gray-200/50 dark:border-gray-700/50">
        <div class="mt-3 text-center">
            <div
                class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-danger-100 dark:bg-danger-900 mb-6">
                <svg class="h-8 w-8 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
            </div>
            <h3 id="deleteModalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                Delete Item
            </h3>
            <div class="mb-8 px-4">
                <p id="deleteModalText" class="text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this item? This action cannot be undone.
                </p>
            </div>
            <div class="flex justify-center space-x-4">
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-danger-500 to-danger-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                        Delete
                    </button>
                </form>
                <button id="cancelDelete"
                    class="px-6 py-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Handle open modal
        document.querySelectorAll('[data-open-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                let url = btn.dataset.url;
                let type = btn.dataset.type || 'item'; // 'lead' or 'callback'
                let title = 'Delete ' + type.charAt(0).toUpperCase() + type.slice(1);
                let text = `Are you sure you want to delete this ${type}? This action cannot be undone.`;

                document.getElementById('deleteForm').setAttribute('action', url);
                document.getElementById('deleteModalTitle').textContent = title;
                document.getElementById('deleteModalText').textContent = text;

                document.getElementById('deleteModal').classList.remove('hidden');
            });
        });

        // Handle cancel
        document.getElementById('cancelDelete').addEventListener('click', () => {
            document.getElementById('deleteModal').classList.add('hidden');
        });
    </script>
@endpush
