<div class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report an Issue</h3>
    
    <form method="POST" action="{{ route('leads.issues.store', $lead) }}" class="space-y-4" enctype="multipart/form-data">
        @csrf
        
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issue Title</label>
            <input type="text" name="title" id="title" required 
                   class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Brief summary of the issue">
        </div>
        
        <div>
            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
            <select name="priority" id="priority" 
                    class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="low">Low</option>
                <option value="normal" selected>Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea name="description" id="description" rows="4" required
                      class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Detailed description of the issue"></textarea>
        </div>
        
        <div>
            <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attachments (Optional)</label>
            <input type="file" name="attachments[]" id="attachments" multiple
                   class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload screenshots or relevant files (max 10MB each)</p>
        </div>
        
        <div class="pt-2">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow hover:shadow-lg transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 5v14m-7-7h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Submit Issue
            </button>
        </div>
    </form>
</div>